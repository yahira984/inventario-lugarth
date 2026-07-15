from __future__ import annotations

import datetime as dt
import hashlib
import json
import re
import unicodedata
from io import BytesIO
from pathlib import Path

import openpyxl
from PIL import Image, ImageOps


SOURCE = Path(r"D:\LUGARTH\LUGARTH (2025).xlsx")
PUBLIC_STORAGE_DIR = Path("storage/app/public/materiales/excel")
MANIFEST = Path("storage/app/imports/lugarth_2025_imagenes_manifest.json")


def normalize(value: object) -> str:
    text = "" if value is None else str(value)
    text = text.replace("\n", " ").replace("\r", " ")
    return re.sub(r"\s+", " ", text).strip()


def ascii_text(value: str) -> str:
    normalized = unicodedata.normalize("NFKD", value)
    return normalized.encode("ascii", "ignore").decode("ascii")


def header_key(value: object) -> str:
    text = ascii_text(normalize(value)).upper()
    return re.sub(r"[^A-Z0-9 ]+", "", text)


def slugify(value: str) -> str:
    text = ascii_text(value).lower()
    text = re.sub(r"[^a-z0-9]+", "-", text).strip("-")
    return text[:90] or "producto"


def find_header(row: tuple[object, ...]) -> bool:
    keys = {header_key(cell) for cell in row if cell is not None}
    return "DESCRIPCION" in keys and any(key in keys for key in ("NO DE PARTE", "NO DE PARTE CODIGO"))


def build_column_map(row: tuple[object, ...]) -> dict[str, int]:
    mapping: dict[str, int] = {}
    for index, value in enumerate(row):
        key = header_key(value)
        if key in {"NO DE PARTE", "NO DE PARTE CODIGO"}:
            mapping["numero_parte"] = index
        elif key == "DESCRIPCION":
            mapping["descripcion"] = index
        elif key == "MARCA":
            mapping["marca"] = index
        elif key == "PROVEEDOR":
            mapping["proveedor"] = index
        elif key in {"FOTOGRAFIA", "IMAGEN"}:
            mapping["fotografia"] = index
    return mapping


def row_value(row: tuple[object, ...], mapping: dict[str, int], key: str) -> object:
    index = mapping.get(key)
    if index is None or index >= len(row):
        return None
    return row[index]


def collect_records(sheet: openpyxl.worksheet.worksheet.Worksheet) -> tuple[dict[int, dict[str, str]], int | None]:
    records: dict[int, dict[str, str]] = {}
    mapping: dict[str, int] | None = None
    photo_column: int | None = None

    for row_number, row in enumerate(sheet.iter_rows(values_only=True), start=1):
        if find_header(row):
            mapping = build_column_map(row)
            photo_column = (mapping["fotografia"] + 1) if "fotografia" in mapping else photo_column
            continue

        if mapping is None:
            continue

        descripcion = normalize(row_value(row, mapping, "descripcion"))
        numero_parte = normalize(row_value(row, mapping, "numero_parte"))

        if not descripcion or header_key(descripcion) in {"DESCRIPCION", "FOTOGRAFIA", "IMAGEN"}:
            continue

        records[row_number] = {
            "categoria": sheet.title.strip(),
            "numero_parte": numero_parte,
            "descripcion": descripcion,
            "marca": normalize(row_value(row, mapping, "marca")),
            "proveedor": normalize(row_value(row, mapping, "proveedor")),
        }

    return records, photo_column


def closest_record(records: dict[int, dict[str, str]], image_row: int) -> tuple[int, dict[str, str]] | None:
    if image_row in records:
        return image_row, records[image_row]

    for offset in (1, -1, 2, -2):
        row = image_row + offset
        if row in records:
            return row, records[row]

    return None


def save_as_png(data: bytes, output: Path) -> None:
    with Image.open(BytesIO(data)) as image:
        image = ImageOps.exif_transpose(image)
        image.thumbnail((1400, 1400), Image.Resampling.LANCZOS)

        if image.mode not in {"RGB", "RGBA"}:
            image = image.convert("RGBA" if "A" in image.getbands() else "RGB")

        output.parent.mkdir(parents=True, exist_ok=True)
        image.save(output, "PNG", optimize=True)


def main() -> None:
    workbook = openpyxl.load_workbook(SOURCE, data_only=True)
    PUBLIC_STORAGE_DIR.mkdir(parents=True, exist_ok=True)
    MANIFEST.parent.mkdir(parents=True, exist_ok=True)

    candidates: dict[tuple[str, int], dict[str, object]] = {}
    skipped: list[dict[str, object]] = []

    for sheet in workbook.worksheets:
        if sheet.title.strip().upper().startswith("ALMAC"):
            continue

        records, photo_column = collect_records(sheet)

        for image in getattr(sheet, "_images", []):
            anchor = image.anchor._from
            image_row = anchor.row + 1
            image_column = anchor.col + 1

            if image_row <= 5:
                skipped.append({"sheet": sheet.title, "row": image_row, "reason": "encabezado/logo"})
                continue

            if photo_column is not None and abs(image_column - photo_column) > 1:
                skipped.append({"sheet": sheet.title, "row": image_row, "column": image_column, "reason": "fuera de columna de fotografia"})
                continue

            match = closest_record(records, image_row)
            if match is None:
                skipped.append({"sheet": sheet.title, "row": image_row, "column": image_column, "reason": "sin producto cercano"})
                continue

            product_row, record = match
            data = image._data()
            key = (sheet.title.strip(), product_row)
            area = int(getattr(image, "width", 0) or 0) * int(getattr(image, "height", 0) or 0)

            current = candidates.get(key)
            if current is not None and int(current["area"]) >= area:
                continue

            candidates[key] = {
                "sheet": sheet.title.strip(),
                "excel_row": product_row,
                "image_anchor_row": image_row,
                "image_anchor_column": image_column,
                "area": area,
                "record": record,
                "data": data,
            }

    images: list[dict[str, object]] = []
    for (_, product_row), candidate in sorted(candidates.items(), key=lambda item: (item[0][0], item[0][1])):
        record = candidate["record"]
        assert isinstance(record, dict)
        data = candidate["data"]
        assert isinstance(data, bytes)

        hash_id = hashlib.sha1(data).hexdigest()[:10]
        base_name = slugify(f"{candidate['sheet']}-{product_row}-{record.get('numero_parte') or record.get('descripcion')}")
        filename = f"{base_name}-{hash_id}.png"
        output = PUBLIC_STORAGE_DIR / filename

        save_as_png(data, output)

        images.append(
            {
                "categoria": record["categoria"],
                "numero_parte": record["numero_parte"],
                "descripcion": record["descripcion"],
                "marca": record["marca"],
                "proveedor": record["proveedor"],
                "excel_row": product_row,
                "image_anchor_row": candidate["image_anchor_row"],
                "image_anchor_column": candidate["image_anchor_column"],
                "fotografia": f"materiales/excel/{filename}",
                "archivo": str(output),
            }
        )

    manifest = {
        "source": str(SOURCE),
        "generated_at": dt.datetime.now().isoformat(timespec="seconds"),
        "total_images": len(images),
        "images": images,
        "skipped": skipped,
    }
    MANIFEST.write_text(json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8")

    print(f"{len(images)} fotos extraidas -> {PUBLIC_STORAGE_DIR}")
    print(f"Manifest -> {MANIFEST}")
    by_sheet: dict[str, int] = {}
    for image in images:
        by_sheet[str(image["categoria"])] = by_sheet.get(str(image["categoria"]), 0) + 1
    for sheet, count in sorted(by_sheet.items()):
        print(f"- {sheet}: {count}")


if __name__ == "__main__":
    main()
