import { mkdir, writeFile } from 'node:fs/promises';

const sampleRate = 16000;
const duration = 16;
const noteLength = 0.8;
const output = new URL('../storage/app/public/musics/', import.meta.url);

const tracks = [
  {
    file: 'bali-romantis.wav',
    notes: [392, 523.25, 587.33, 783.99, 587.33, 523.25, 440, 523.25, 659.25, 783.99],
    pulse: 196,
  },
  {
    file: 'janji-suci.wav',
    notes: [293.66, 392, 440, 587.33, 523.25, 440, 392, 329.63, 392, 523.25],
    pulse: 146.83,
  },
  {
    file: 'senja-bahagia.wav',
    notes: [329.63, 493.88, 659.25, 587.33, 493.88, 392, 493.88, 659.25, 783.99, 659.25],
    pulse: 164.81,
  },
];

function envelope(time) {
  const local = time % noteLength;
  return Math.exp(-local * 3.7) * Math.min(1, local * 45);
}

function sample(track, time) {
  const note = track.notes[Math.floor(time / noteLength) % track.notes.length];
  const strike = envelope(time);
  const shimmer = Math.sin(2 * Math.PI * note * time)
    + 0.38 * Math.sin(2 * Math.PI * note * 2.01 * time)
    + 0.18 * Math.sin(2 * Math.PI * note * 3.98 * time);
  const drone = 0.12 * Math.sin(2 * Math.PI * track.pulse * time) * (0.6 + 0.4 * Math.sin(2 * Math.PI * time / 4));
  const softPad = 0.07 * Math.sin(2 * Math.PI * track.pulse / 2 * time);
  return Math.max(-1, Math.min(1, (shimmer * strike * 0.37) + drone + softPad));
}

function createWav(track) {
  const totalSamples = sampleRate * duration;
  const dataSize = totalSamples * 2;
  const buffer = Buffer.alloc(44 + dataSize);
  buffer.write('RIFF', 0);
  buffer.writeUInt32LE(36 + dataSize, 4);
  buffer.write('WAVE', 8);
  buffer.write('fmt ', 12);
  buffer.writeUInt32LE(16, 16);
  buffer.writeUInt16LE(1, 20);
  buffer.writeUInt16LE(1, 22);
  buffer.writeUInt32LE(sampleRate, 24);
  buffer.writeUInt32LE(sampleRate * 2, 28);
  buffer.writeUInt16LE(2, 32);
  buffer.writeUInt16LE(16, 34);
  buffer.write('data', 36);
  buffer.writeUInt32LE(dataSize, 40);
  for (let i = 0; i < totalSamples; i += 1) {
    buffer.writeInt16LE(Math.round(sample(track, i / sampleRate) * 32760), 44 + i * 2);
  }
  return buffer;
}

await mkdir(output, { recursive: true });
for (const track of tracks) {
  await writeFile(new URL(track.file, output), createWav(track));
}
