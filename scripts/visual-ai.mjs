import fs from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import { env, pipeline, RawImage } from '@huggingface/transformers';

const SEMANTIC_MODEL = 'Xenova/clip-vit-base-patch32';
const DETAIL_MODEL = 'Xenova/dinov2-small';
const VERSION = 'clip-vit-b32-dinov2s-q8-v1';
const CACHE_DIR = process.env.VISUAL_AI_CACHE
    ? path.resolve(process.env.VISUAL_AI_CACHE)
    : path.resolve('storage/app/visual-ai/models');

env.cacheDir = CACHE_DIR;
env.allowRemoteModels = process.env.VISUAL_AI_ALLOW_DOWNLOAD === '1';
env.allowLocalModels = true;

let semanticExtractorPromise;
let detailExtractorPromise;

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

function normalize(values) {
    const magnitude = Math.sqrt(values.reduce((sum, value) => sum + (value * value), 0));

    if (!Number.isFinite(magnitude) || magnitude === 0) {
        throw new Error('El modelo devolvio una huella visual vacia.');
    }

    return values.map((value) => Number((value / magnitude).toFixed(7)));
}

async function embedImage(imagePath) {
    const absolutePath = path.resolve(imagePath);
    const image = await RawImage.read(absolutePath);
    const semanticOutput = await (await semanticExtractor())(image);
    const detailOutput = await (await detailExtractor())(image);
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
    process.stdout.write(JSON.stringify({
        ok: true,
        models: [SEMANTIC_MODEL, DETAIL_MODEL],
        version: VERSION,
        cache: CACHE_DIR,
    }));
}

async function embed(imagePath) {
    process.stdout.write(JSON.stringify(await embedImage(imagePath)));
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
                ...(await embedImage(item.path)),
            });
        } catch (error) {
            results.push({
                id: item.id,
                signature: item.signature ?? null,
                error: error instanceof Error ? error.message : String(error),
            });
        }
    }

    process.stdout.write(JSON.stringify(results));
}

const [command, argument] = process.argv.slice(2);

try {
    if (command === 'setup') {
        await setup();
    } else if (command === 'embed' && argument) {
        await embed(argument);
    } else if (command === 'batch' && argument) {
        await batch(argument);
    } else {
        throw new Error('Uso: visual-ai.mjs setup | embed <imagen> | batch <manifiesto.json>');
    }
} catch (error) {
    process.stderr.write(error instanceof Error ? error.message : String(error));
    process.exitCode = 1;
}
