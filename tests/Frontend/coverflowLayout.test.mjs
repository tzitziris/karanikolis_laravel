import assert from 'node:assert/strict';
import test from 'node:test';
import { coverflowLayout } from '../../resources/js/animation/coverflowLayout.js';

test('the centre and both depth stops match the design contract', () => {
    assert.deepEqual(coverflowLayout(0), { x: 0, rotateY: 0, z: 0, scale: 1, overlay: 0, opacity: 1 });
    assert.deepEqual(coverflowLayout(1), { x: 62, rotateY: -38, z: -220, scale: .86, overlay: .55, opacity: 1 });
    assert.deepEqual(coverflowLayout(2), { x: 104, rotateY: -52, z: -420, scale: .74, overlay: .8, opacity: 1 });
});

test('continuous progress is symmetric and bounded for rosters of 2 through 10', () => {
    for (let count = 2; count <= 10; count++) {
        for (let progress = 0; progress <= count - 1; progress += .03125) {
            for (let index = 0; index < count; index++) {
                const offset = index - progress;
                const a = coverflowLayout(offset);
                const b = coverflowLayout(-offset);
                assert.equal(a.x + b.x, 0);
                assert.equal(a.rotateY + b.rotateY, 0);
                for (const key of ['z', 'scale', 'overlay', 'opacity']) assert.equal(a[key], b[key]);
                assert.ok(a.opacity >= 0 && a.opacity <= 1);
                assert.ok(a.scale >= .74 && a.scale <= 1);
                if (Math.abs(offset) <= 2) assert.equal(a.opacity, 1);
                if (Math.abs(offset) >= 3) assert.equal(a.opacity, 0);
            }
        }
    }
});

test('crossing a stop introduces neither a value jump nor a velocity jump', () => {
    const epsilon = .00001;
    for (const stop of [-3, -2, -1, 0, 1, 2, 3]) {
        const left = coverflowLayout(stop - epsilon);
        const centre = coverflowLayout(stop);
        const right = coverflowLayout(stop + epsilon);
        for (const key of Object.keys(centre)) {
            assert.ok(Math.abs(left[key] - right[key]) < .00001, `${stop}: ${key} value`);
            assert.ok(Math.abs((centre[key] - left[key]) / epsilon - (right[key] - centre[key]) / epsilon) < .03, `${stop}: ${key} velocity`);
        }
    }
});
