// Percentages are relative to the card. Smoothstep has zero velocity at each
// stop, including the change of rotation at the centre.
export function coverflowLayout(offset) {
    const distance = Math.abs(offset);
    const direction = Math.sign(offset);
    const stops = [
        { x: 0, rotateY: 0, z: 0, scale: 1, overlay: 0 },
        { x: 62, rotateY: -38, z: -220, scale: 0.86, overlay: 0.55 },
        { x: 104, rotateY: -52, z: -420, scale: 0.74, overlay: 0.8 },
    ];
    const segment = Math.min(Math.floor(distance), 1);
    const fraction = Math.min(distance - segment, 1);
    const blend = fraction * fraction * (3 - 2 * fraction);
    const value = (key) => stops[segment][key]
        + (stops[segment + 1][key] - stops[segment][key]) * blend;
    const fade = Math.min(Math.max(distance - 2, 0), 1);

    return {
        x: direction * value('x'),
        rotateY: direction * value('rotateY'),
        z: value('z'),
        scale: value('scale'),
        overlay: value('overlay'),
        opacity: 1 - fade * fade * (3 - 2 * fade),
    };
}
