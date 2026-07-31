import fs from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import { env, pipeline, RawImage } from '@huggingface/transformers';

const SEMANTIC_MODEL = 'Xenova/clip-vit-base-patch32';
const DETAIL_MODEL = 'Xenova/dinov2-small';
const VERSION = 'clip-aspect-dinov2-square-v3';
const CACHE_DIR = process.env.VISUAL_AI_CACHE
    ? path.resolve(process.env.VISUAL_AI_CACHE)
    : path.resolve('storage/app/visual-ai/models');

env.cacheDir = CACHE_DIR;
env.allowRemoteModels = process.env.VISUAL_AI_ALLOW_DOWNLOAD === '1';
env.allowLocalModels = true;

let semanticExtractorPromise;
let detailExtractorPromise;
let categoryClassifierPromise;

async function writeResult(value) {
    const serialized = JSON.stringify(value);

    if (process.env.VISUAL_AI_OUTPUT) {
        await fs.writeFile(path.resolve(process.env.VISUAL_AI_OUTPUT), serialized, 'utf8');

        return;
    }

    process.stdout.write(serialized);
}

async function writeError(value) {
    const message = value instanceof Error ? value.message : String(value);

    if (process.env.VISUAL_AI_ERROR) {
        await fs.writeFile(path.resolve(process.env.VISUAL_AI_ERROR), message, 'utf8');

        return;
    }

    process.stderr.write(message);
}

function semanticExtractor() {
    semanticExtractorPromise ??= pipeline('image-feature-extraction', SEMANTIC_MODEL, {
        dtype: 'q8',
    });

    return semanticExtractorPromise;
}

function detailExtractor() {
    detailExtractorPromise ??= pipeline('image-feature-extraction', DETAIL_MODEL, {
        dtype: 'q8',
    });

    return detailExtractorPromise;
}

function categoryClassifier() {
    categoryClassifierPromise ??= pipeline('zero-shot-image-classification', SEMANTIC_MODEL, {
        dtype: 'q8',
    });

    return categoryClassifierPromise;
}

function normalize(values) {
    const magnitude = Math.sqrt(values.reduce((sum, value) => sum + (value * value), 0));

    if (!Number.isFinite(magnitude) || magnitude === 0) {
        throw new Error('El modelo devolvio una huella visual vacia.');
    }

    return values.map((value) => Number((value / magnitude).toFixed(7)));
}

async function embedImage(semanticImagePath, detailImagePath = semanticImagePath) {
    const [semanticImage, detailImage] = await Promise.all([
        RawImage.read(path.resolve(semanticImagePath)),
        RawImage.read(path.resolve(detailImagePath)),
    ]);
    const semanticOutput = await (await semanticExtractor())(semanticImage);
    const detailOutput = await (await detailExtractor())(detailImage);
    const detailSize = Number(detailOutput.dims.at(-1));

    if (!semanticOutput.data.length || !detailSize || detailOutput.data.length < detailSize) {
        throw new Error('Los modelos visuales no devolvieron el formato esperado.');
    }

    return {
        version: VERSION,
        dimensions: semanticOutput.data.length,
        embedding: normalize(Array.from(semanticOutput.data)),
        detail_dimensions: detailSize,
        detail_embedding: normalize(Array.from(detailOutput.data.slice(0, detailSize))),
    };
}

async function setup() {
    await Promise.all([semanticExtractor(), detailExtractor()]);
    await writeResult({
        ok: true,
        models: [SEMANTIC_MODEL, DETAIL_MODEL],
        version: VERSION,
        cache: CACHE_DIR,
    });
}

async function embed(semanticImagePath, detailImagePath) {
    await writeResult(await embedImage(semanticImagePath, detailImagePath));
}

async function batch(manifestPath) {
    const decoded = JSON.parse(await fs.readFile(path.resolve(manifestPath), 'utf8'));
    const manifest = Array.isArray(decoded) ? decoded : Object.values(decoded);
    const results = [];

    await Promise.all([semanticExtractor(), detailExtractor()]);

    for (const item of manifest) {
        try {
            results.push({
                id: item.id,
                signature: item.signature ?? null,
                ...(await embedImage(
                    item.semantic_path ?? item.path,
                    item.detail_path ?? item.path,
                )),
            });
        } catch (error) {
            results.push({
                id: item.id,
                signature: item.signature ?? null,
                error: error instanceof Error ? error.message : String(error),
            });
        }
    }

    await writeResult(results);
}

async function categorize(imagePath, labelsJson) {
    const labels = JSON.parse(labelsJson);
    if (!Array.isArray(labels) || labels.length === 0) {
        throw new Error('Se requiere al menos una categoria para clasificar.');
    }

    const image = await RawImage.read(path.resolve(imagePath));
    const results = await (await categoryClassifier())(image, labels.slice(0, 45));
    const ranked = Array.isArray(results) ? results : (results?.results ?? []);
    await writeResult(ranked.map((item) => ({
        label: String(item.label ?? ''),
        score: Number(item.score ?? 0),
    })));
}

const [command, argument, detailArgument] = process.argv.slice(2);

try {
    if (command === 'setup') {
        await setup();
    } else if (command === 'embed' && argument) {
        await embed(argument, detailArgument);
    } else if (command === 'batch' && argument) {
        await batch(argument);
    } else if (command === 'categorize' && argument && detailArgument) {
        await categorize(argument, detailArgument);
    } else {
        throw new Error('Uso: visual-ai.mjs setup | embed <imagen-semantica> [imagen-detalle] | batch <manifiesto.json> | categorize <imagen> <categorias-json>');
    }
} catch (error) {
    await writeError(error);
    process.exitCode = 1;
}
