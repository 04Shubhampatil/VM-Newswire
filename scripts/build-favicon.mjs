// Renders public/favicon.svg into a real favicon.ico (PNG-in-ICO, 16/32/48) and apple-touch-icon.png.
import sharp from 'sharp';
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../public', import.meta.url));
const svg = readFileSync(`${root}/favicon.svg`);

const sizes = [16, 32, 48];
const pngs = [];
for (const size of sizes) {
    pngs.push(await sharp(svg, { density: 384 }).resize(size, size, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } }).png().toBuffer());
}

// ICO container: header (6) + directory entries (16 each) + PNG payloads.
const header = Buffer.alloc(6);
header.writeUInt16LE(0, 0); // reserved
header.writeUInt16LE(1, 2); // type: icon
header.writeUInt16LE(sizes.length, 4);
const dir = Buffer.alloc(16 * sizes.length);
let offset = 6 + dir.length;
sizes.forEach((size, i) => {
    const e = i * 16;
    dir.writeUInt8(size === 256 ? 0 : size, e);
    dir.writeUInt8(size === 256 ? 0 : size, e + 1);
    dir.writeUInt8(0, e + 2); // palette
    dir.writeUInt8(0, e + 3); // reserved
    dir.writeUInt16LE(1, e + 4); // planes
    dir.writeUInt16LE(32, e + 6); // bpp
    dir.writeUInt32LE(pngs[i].length, e + 8);
    dir.writeUInt32LE(offset, e + 12);
    offset += pngs[i].length;
});
writeFileSync(`${root}/favicon.ico`, Buffer.concat([header, dir, ...pngs]));

await sharp(svg, { density: 384 })
    .resize(180, 180, { fit: 'contain', background: '#ffffff' })
    .flatten({ background: '#ffffff' })
    .png()
    .toFile(`${root}/apple-touch-icon.png`);

console.log('favicon.ico bytes:', 6 + dir.length + pngs.reduce((n, b) => n + b.length, 0));
