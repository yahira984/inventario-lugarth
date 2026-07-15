from __future__ import annotations

import datetime as dt
import decimal
import re
from pathlib import Path

import openpyxl


SOURCE = Path(r"D:\LUGARTH\LUGARTH (2025).xlsx")
TARGET = Path("storage/app/imports/lugarth_2025_materiales.sql")


def normalize(value: object) -> str:
    text = "" if value is None else str(value)
    text = text.replace("\n", " ").replace("\r", " ")
    return re.sub(r"\s+", " ", text).strip()


def header_key(value: object) -> str:
    text = normalize(value).upper()
    text = text.replace("Á", "A").replace("É", "E").replace("Í", "I").replace("Ó", "O").replace("Ú", "U")
    return re.sub(r"[^A-Z0-9 ]+", "", text)


def clean_number(value: object) -> str:
    if value is None or value == "":
        return "0"

    if isinstance(value, (int, float, decimal.Decimal)):
        return str(decimal.Decimal(str(value)).quantize(decimal.Decimal("0.01")))

    text = normalize(value)
    text = re.sub(r"[^0-9.\-]", "", text)
    if text in {"", "-", ".", "-."}:
        return "0"

    try:
        return str(decimal.Decimal(text).quantize(decimal.Decimal("0.01")))
    except decimal.InvalidOperation:
        return "0"


def clean_int(value: object) -> int:
    if value is None or value == "":
        return 0
    if isinstance(value, (int, float)):
        return max(0, int(value))

    text = normalize(value)
    match = re.search(r"\d+", text)
    return int(match.group(0)) if match else 0


def sql_string(value: object) -> str:
    text = normalize(value)
    if not text:
        return "NULL"
    return "'" + text.replace("\\", "\\\\").replace("'", "''") + "'"


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
        elif key == "CANTIDAD X KIT":
            mapping["stock"] = index
        elif key in {"UNIDAD DE MEDIDA", "UNIDAD"}:
            mapping["unidad"] = index
        elif key == "PRECIO":
            mapping["costo_unitario"] = index
        elif key == "ALMACEN":
            mapping["almacen"] = index
    return mapping


def row_value(row: tuple[object, ...], mapping: dict[str, int], key: str) -> object:
    index = mapping.get(key)
    if index is None or index >= len(row):
        return None
    return row[index]


def main() -> None:
    workbook = openpyxl.load_workbook(SOURCE, read_only=True, data_only=True)
    records: list[dict[str, object]] = []
    seen: set[tuple[str, str, str]] = set()

    for sheet in workbook.worksheets:
        if sheet.title.strip().upper().startswith("ALMAC"):
            continue

        rows = sheet.iter_rows(values_only=True)
        mapping: dict[str, int] | None = None

        for row in rows:
            if mapping is None:
                if find_header(row):
                    mapping = build_column_map(row)
                continue

            descripcion = normalize(row_value(row, mapping, "descripcion"))
            numero_parte = normalize(row_value(row, mapping, "numero_parte"))

            if not descripcion or header_key(descripcion) in {"DESCRIPCION", "FOTOGRAFIA"}:
                continue

            key = (sheet.title.strip().upper(), numero_parte.upper(), descripcion.upper())
            if key in seen:
                continue
            seen.add(key)

            stock = clean_int(row_value(row, mapping, "stock"))
            records.append(
                {
                    "categoria": sheet.title.strip(),
                    "almacen": row_value(row, mapping, "almacen") or "Almacen principal",
                    "numero_parte": numero_parte or None,
                    "descripcion": descripcion,
                    "marca": row_value(row, mapping, "marca"),
                    "proveedor": row_value(row, mapping, "proveedor"),
                    "stock": stock,
                    "stock_minimo": 0,
                    "stock_maximo": 0,
                    "costo_unitario": clean_number(row_value(row, mapping, "costo_unitario")),
                    "unidad": row_value(row, mapping, "unidad"),
                }
            )

    TARGET.parent.mkdir(parents=True, exist_ok=True)
    today = dt.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    columns = [
        "categoria",
        "almacen",
        "numero_parte",
        "codigo_barras",
        "clave_sat",
        "clave_unidad",
        "unidad",
        "descripcion",
        "marca",
        "proveedor",
        "proveedor_rfc",
        "stock",
        "stock_minimo",
        "stock_maximo",
        "costo_unitario",
        "moneda",
        "fotografia",
        "evidencia_foto",
        "created_at",
        "updated_at",
    ]

    lines = [
        "-- Importacion inicial desde D:\\LUGARTH\\LUGARTH (2025).xlsx",
        "-- Generado para DBeaver. Codigos de barras e imagenes quedan vacios para completarlos despues.",
        "SET FOREIGN_KEY_CHECKS=0;",
        "",
    ]

    for record in records:
        values = [
            sql_string(record["categoria"]),
            sql_string(record["almacen"]),
            sql_string(record["numero_parte"]),
            "NULL",
            "NULL",
            "NULL",
            sql_string(record["unidad"]),
            sql_string(record["descripcion"]),
            sql_string(record["marca"]),
            sql_string(record["proveedor"]),
            "NULL",
            str(record["stock"]),
            str(record["stock_minimo"]),
            str(record["stock_maximo"]),
            str(record["costo_unitario"]),
            "'MXN'",
            "NULL",
            "NULL",
            f"'{today}'",
            f"'{today}'",
        ]
        lines.append(f"INSERT INTO `materials` (`{'`, `'.join(columns)}`) VALUES ({', '.join(values)});")

    lines.extend(["", "SET FOREIGN_KEY_CHECKS=1;", f"-- Total de productos generados: {len(records)}", ""])
    TARGET.write_text("\n".join(lines), encoding="utf-8")
    print(f"{len(records)} productos -> {TARGET}")


if __name__ == "__main__":
    main()
