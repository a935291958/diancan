/**
 * 生成微信小程序 tabBar 所需 PNG 图标（81x81）
 */
const fs = require('fs')
const path = require('path')
const zlib = require('zlib')

const SIZE = 81

function crc32(buf) {
  let crc = 0xffffffff
  for (let i = 0; i < buf.length; i++) {
    crc ^= buf[i]
    for (let j = 0; j < 8; j++) {
      crc = (crc >>> 1) ^ (crc & 1 ? 0xedb88320 : 0)
    }
  }
  return (crc ^ 0xffffffff) >>> 0
}

function chunk(type, data) {
  const typeBuf = Buffer.from(type)
  const len = Buffer.alloc(4)
  len.writeUInt32BE(data.length, 0)
  const crcBuf = Buffer.alloc(4)
  crcBuf.writeUInt32BE(crc32(Buffer.concat([typeBuf, data])), 0)
  return Buffer.concat([len, typeBuf, data, crcBuf])
}

function encodePng(pixels) {
  const raw = Buffer.alloc((SIZE * 4 + 1) * SIZE)
  for (let y = 0; y < SIZE; y++) {
    const rowStart = y * (SIZE * 4 + 1)
    raw[rowStart] = 0
    pixels.copy(raw, rowStart + 1, y * SIZE * 4, (y + 1) * SIZE * 4)
  }
  const ihdr = Buffer.alloc(13)
  ihdr.writeUInt32BE(SIZE, 0)
  ihdr.writeUInt32BE(SIZE, 4)
  ihdr[8] = 8
  ihdr[9] = 6
  const png = Buffer.concat([
    Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]),
    chunk('IHDR', ihdr),
    chunk('IDAT', zlib.deflateSync(raw)),
    chunk('IEND', Buffer.alloc(0)),
  ])
  return png
}

function hexToRgb(hex) {
  const n = parseInt(hex.slice(1), 16)
  return [(n >> 16) & 255, (n >> 8) & 255, n & 255]
}

function drawIcon(colorHex, glyph) {
  const [r, g, b] = hexToRgb(colorHex)
  const pixels = Buffer.alloc(SIZE * SIZE * 4, 0)
  const cx = (SIZE - 1) / 2
  const cy = (SIZE - 1) / 2

  function setPx(x, y, a = 255) {
    if (x < 0 || y < 0 || x >= SIZE || y >= SIZE) return
    const i = (y * SIZE + x) * 4
    pixels[i] = r
    pixels[i + 1] = g
    pixels[i + 2] = b
    pixels[i + 3] = a
  }

  function fillCircle(ox, oy, rad) {
    const rr = rad * rad
    for (let y = 0; y < SIZE; y++) {
      for (let x = 0; x < SIZE; x++) {
        const dx = x - ox
        const dy = y - oy
        if (dx * dx + dy * dy <= rr) setPx(x, y)
      }
    }
  }

  function fillRect(x0, y0, x1, y1) {
    for (let y = y0; y <= y1; y++) {
      for (let x = x0; x <= x1; x++) setPx(x, y)
    }
  }

  if (glyph === 'home') {
    for (let y = 18; y <= 42; y++) {
      const w = Math.floor((y - 18) * 1.35)
      fillRect(Math.floor(cx - w), y, Math.floor(cx + w), y)
    }
    fillRect(28, 42, 52, 64)
  } else if (glyph === 'menu') {
    fillRect(22, 24, 58, 30)
    fillRect(22, 38, 58, 44)
    fillRect(22, 52, 58, 58)
  } else if (glyph === 'order') {
    fillRect(24, 18, 57, 64)
    fillRect(32, 28, 49, 32)
    fillRect(32, 40, 49, 44)
    fillRect(32, 52, 44, 56)
  } else if (glyph === 'family') {
    fillCircle(32, 30, 8)
    fillCircle(50, 30, 8)
    fillCircle(41, 52, 10)
  } else {
    fillCircle(cx, 28, 10)
    fillCircle(cx, 58, 16)
  }

  return encodePng(pixels)
}

const outDir = path.join(__dirname, '../src/static/tabbar')
fs.mkdirSync(outDir, { recursive: true })

const glyphs = ['home', 'menu', 'order', 'family', 'mine']
glyphs.forEach((name) => {
  fs.writeFileSync(path.join(outDir, `${name}.png`), drawIcon('#909399', name))
  fs.writeFileSync(path.join(outDir, `${name}-active.png`), drawIcon('#FF6B35', name))
})

console.log('tabbar icons generated')
