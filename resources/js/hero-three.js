import * as THREE from 'three';

export function mountHeroScene(container) {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (reduceMotion.matches || !window.WebGLRenderingContext) return;

    let renderer;
    try {
        renderer = new THREE.WebGLRenderer({
            alpha: true,
            antialias: true,
            powerPreference: 'low-power',
        });
    } catch {
        return;
    }

    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
    renderer.setClearColor(0x000000, 0);
    container.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(34, 1, 0.1, 100);
    camera.position.set(0, 0, 11);
    scene.add(new THREE.AmbientLight(0xffffff, 2.4));
    const light = new THREE.DirectionalLight(0xffffff, 3);
    light.position.set(-3, 5, 8);
    scene.add(light);

    const terracotta = new THREE.MeshStandardMaterial({
        color: 0xc76545,
        roughness: 0.55,
        metalness: 0,
    });
    const cream = new THREE.MeshStandardMaterial({ color: 0xfffaf4, roughness: 0.8, metalness: 0 });
    const outline = new THREE.MeshStandardMaterial({
        color: 0xa94f34,
        roughness: 0.6,
        metalness: 0,
    });
    const group = new THREE.Group();
    scene.add(group);

    const locator = new THREE.Group();
    const rim = new THREE.Mesh(new THREE.TorusGeometry(1.15, 0.24, 14, 64), terracotta);
    locator.add(rim);
    const dot = new THREE.Mesh(new THREE.SphereGeometry(0.34, 24, 24), cream);
    dot.position.set(0, 0, 0.12);
    locator.add(dot);
    const handle = new THREE.Mesh(new THREE.CylinderGeometry(0.18, 0.18, 1.55, 18), terracotta);
    handle.rotation.z = Math.PI / 4;
    handle.position.set(1.22, -1.22, 0);
    locator.add(handle);
    locator.position.set(0.5, 0.6, 0);
    group.add(locator);

    const ring = new THREE.Mesh(new THREE.TorusGeometry(2.55, 0.018, 6, 80), outline);
    ring.rotation.set(0.42, 0.24, 0.23);
    group.add(ring);
    const sparks = [
        [-2.1, 1.55, 0.4, 0.095],
        [2.7, 1.8, -0.2, 0.13],
        [-2.55, -0.7, -0.4, 0.07],
        [2.6, -1.6, 0.2, 0.09],
    ];
    for (const [x, y, z, size] of sparks) {
        const spark = new THREE.Mesh(new THREE.SphereGeometry(size, 12, 12), terracotta);
        spark.position.set(x, y, z);
        group.add(spark);
    }

    let frame = 0;
    let visible = true;
    let targetX = 0;
    let targetY = 0;
    const startTime = performance.now();
    const resize = () => {
        const { width, height } = container.getBoundingClientRect();
        if (!width || !height) return;
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        group.scale.setScalar(Math.min(width / 410, height / 390, 1.15));
        renderer.render(scene, camera);
    };
    const animate = () => {
        if (!visible || reduceMotion.matches) return;
        const time = (performance.now() - startTime) / 1000;
        group.rotation.y += (targetX - group.rotation.y) * 0.035;
        group.rotation.x += (targetY - group.rotation.x) * 0.035;
        locator.position.y = 0.6 + Math.sin(time * 1.15) * 0.12;
        ring.rotation.z = 0.23 + Math.sin(time * 0.38) * 0.14;
        renderer.render(scene, camera);
        frame = requestAnimationFrame(animate);
    };
    const onPointer = (event) => {
        const bounds = container.getBoundingClientRect();
        targetX = ((event.clientX - bounds.left) / bounds.width - 0.5) * 0.28;
        targetY = ((event.clientY - bounds.top) / bounds.height - 0.5) * 0.18;
    };
    const onVisibility = () => {
        visible = !document.hidden;
        if (visible) animate();
        else cancelAnimationFrame(frame);
    };
    const onMotionChange = () => {
        if (reduceMotion.matches) {
            cancelAnimationFrame(frame);
            renderer.domElement.style.display = 'none';
        } else {
            renderer.domElement.style.display = '';
            if (visible) animate();
        }
    };
    const observer = new ResizeObserver(resize);
    observer.observe(container);
    container.addEventListener('pointermove', onPointer);
    document.addEventListener('visibilitychange', onVisibility);
    reduceMotion.addEventListener('change', onMotionChange);
    resize();
    animate();
}
