import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
	initializeThreeScenes();
	initializeTiltCards();

	const counterInputs = document.querySelectorAll('[data-counter-target]');

	counterInputs.forEach((input) => {
		const targetId = input.getAttribute('data-counter-target');
		const target = targetId ? document.getElementById(targetId) : null;

		if (!target) {
			return;
		}

		const updateCount = () => {
			target.textContent = String(input.value.length);
		};

		input.addEventListener('input', updateCount);
		updateCount();
	});

	const imageInput = document.getElementById('image');
	const imageLabel = document.getElementById('imageName');

	if (imageInput && imageLabel) {
		imageInput.addEventListener('change', () => {
			imageLabel.textContent = imageInput.files?.[0]?.name ?? 'No file selected';
		});
	}
});

async function initializeThreeScenes() {
	const canvases = [...document.querySelectorAll('[data-three-model]')];

	if (!canvases.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	const THREE = await import('three');
	const scenes = canvases
		.map((canvas) => createThreeScene(canvas, canvas.dataset.threeModel ?? 'hero', THREE))
		.filter(Boolean);

	if (!scenes.length) {
		return;
	}

	const handleResize = () => {
		scenes.forEach((sceneState) => resizeThreeScene(sceneState));
	};

	handleResize();
	window.addEventListener('resize', handleResize);

	const clock = new THREE.Clock();

	const render = () => {
		const elapsed = clock.getElapsedTime();

		scenes.forEach((sceneState) => {
			renderThreeScene(sceneState, elapsed);
		});

		window.requestAnimationFrame(render);
	};

	render();
}

function createThreeScene(canvas, type, THREE) {
	const scene = new THREE.Scene();
	const camera = new THREE.PerspectiveCamera(42, 1, 0.1, 100);
	camera.position.set(0, 0, 7);

	const renderer = new THREE.WebGLRenderer({
		canvas,
		alpha: true,
		antialias: true,
	});
	renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

	const group = new THREE.Group();
	scene.add(group);

	const particles = createParticleCloud(THREE, type);
	scene.add(particles);

	scene.add(new THREE.AmbientLight(0xb9f8ff, 0.75));

	const keyLight = new THREE.DirectionalLight(0xffffff, 1.2);
	keyLight.position.set(4, 4, 7);
	scene.add(keyLight);

	const rimLight = new THREE.PointLight(0xff9a5d, 1.1, 25);
	rimLight.position.set(-4, -2, 3);
	scene.add(rimLight);

	const model = buildModel(type, group, THREE);
	if (!model) {
		return null;
	}

	const pointer = { x: 0, y: 0 };
	const wrapper = canvas.closest('[data-three-wrapper]');

	if (wrapper) {
		wrapper.addEventListener('pointermove', (event) => {
			const rect = wrapper.getBoundingClientRect();
			pointer.x = ((event.clientX - rect.left) / rect.width) - 0.5;
			pointer.y = ((event.clientY - rect.top) / rect.height) - 0.5;
		});

		wrapper.addEventListener('pointerleave', () => {
			pointer.x = 0;
			pointer.y = 0;
		});
	}

	return {
		canvas,
		renderer,
		scene,
		camera,
		group,
		particles,
		model,
		pointer,
		type,
	};
}

function createParticleCloud(THREE, type) {
	const particleCount = type === 'hero' ? 900 : 420;
	const spread = type === 'hero' ? 9 : 6;
	const positions = new Float32Array(particleCount * 3);

	for (let i = 0; i < particleCount; i += 1) {
		const radius = spread * Math.sqrt(Math.random());
		const theta = Math.random() * Math.PI * 2;
		const phi = Math.acos((Math.random() * 2) - 1);

		positions[i * 3] = radius * Math.sin(phi) * Math.cos(theta);
		positions[(i * 3) + 1] = radius * Math.sin(phi) * Math.sin(theta);
		positions[(i * 3) + 2] = radius * Math.cos(phi);
	}

	const geometry = new THREE.BufferGeometry();
	geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

	return new THREE.Points(
		geometry,
		new THREE.PointsMaterial({
			color: 0xffffff,
			size: type === 'hero' ? 0.03 : 0.025,
			transparent: true,
			opacity: type === 'hero' ? 0.75 : 0.6,
		})
	);
}

function buildModel(type, group, THREE) {
	if (type === 'atom') {
		const nucleus = new THREE.Mesh(
			new THREE.SphereGeometry(1.05, 24, 24),
			new THREE.MeshStandardMaterial({
				color: 0x4af8ff,
				emissive: 0x0a4465,
				metalness: 0.35,
				roughness: 0.25,
			})
		);

		const ringA = new THREE.Mesh(
			new THREE.TorusGeometry(1.9, 0.06, 14, 120),
			new THREE.MeshBasicMaterial({ color: 0xff8f54, transparent: true, opacity: 0.8 })
		);
		ringA.rotation.x = 1.2;

		const ringB = ringA.clone();
		ringB.rotation.x = 0.2;
		ringB.rotation.y = 0.7;

		const ringC = ringA.clone();
		ringC.rotation.x = 0.8;
		ringC.rotation.z = 1;

		group.add(nucleus, ringA, ringB, ringC);

		return { nucleus, ringA, ringB, ringC };
	}

	if (type === 'galaxy') {
		const knot = new THREE.Mesh(
			new THREE.TorusKnotGeometry(1.3, 0.38, 120, 16),
			new THREE.MeshStandardMaterial({
				color: 0x94ff5c,
				emissive: 0x1d4f2a,
				metalness: 0.4,
				roughness: 0.23,
			})
		);

		const shell = new THREE.Mesh(
			new THREE.TorusGeometry(2.35, 0.05, 14, 130),
			new THREE.MeshBasicMaterial({ color: 0x53e8ff, transparent: true, opacity: 0.5 })
		);
		shell.rotation.x = 0.7;
		shell.rotation.y = 0.4;

		group.add(knot, shell);

		return { knot, shell };
	}

	if (type === 'crystal') {
		const crystal = new THREE.Mesh(
			new THREE.OctahedronGeometry(1.5, 1),
			new THREE.MeshStandardMaterial({
				color: 0x5bd9ff,
				emissive: 0x0c3f61,
				metalness: 0.25,
				roughness: 0.22,
				flatShading: true,
			})
		);

		const frame = new THREE.Mesh(
			new THREE.IcosahedronGeometry(2.2, 1),
			new THREE.MeshBasicMaterial({ color: 0xff9f61, transparent: true, opacity: 0.35, wireframe: true })
		);

		group.add(crystal, frame);

		return { crystal, frame };
	}

	const core = new THREE.Mesh(
		new THREE.IcosahedronGeometry(1.35, 1),
		new THREE.MeshStandardMaterial({
			color: 0x33fff5,
			emissive: 0x004a61,
			metalness: 0.4,
			roughness: 0.2,
			flatShading: true,
		})
	);

	const ring = new THREE.Mesh(
		new THREE.TorusGeometry(2.1, 0.08, 16, 120),
		new THREE.MeshBasicMaterial({ color: 0xff7f3f, transparent: true, opacity: 0.7 })
	);
	ring.rotation.x = 1.2;
	ring.rotation.y = 0.5;

	const ringTwo = new THREE.Mesh(
		new THREE.TorusGeometry(2.8, 0.05, 16, 120),
		new THREE.MeshBasicMaterial({ color: 0xa8ff72, transparent: true, opacity: 0.35 })
	);
	ringTwo.rotation.x = 0.4;
	ringTwo.rotation.z = 0.5;

	group.add(core, ring, ringTwo);

	return { core, ring, ringTwo };
}

function resizeThreeScene(sceneState) {
	const width = sceneState.canvas.clientWidth;
	const height = sceneState.canvas.clientHeight;

	if (!width || !height) {
		return;
	}

	sceneState.renderer.setSize(width, height, false);
	sceneState.camera.aspect = width / height;
	sceneState.camera.updateProjectionMatrix();
}

function renderThreeScene(sceneState, elapsed) {
	sceneState.particles.rotation.y = elapsed * (sceneState.type === 'hero' ? 0.035 : 0.02);

	if (sceneState.type === 'atom') {
		sceneState.model.nucleus.rotation.x = elapsed * 0.35;
		sceneState.model.nucleus.rotation.y = elapsed * 0.55;
		sceneState.model.ringA.rotation.z = elapsed * 0.35;
		sceneState.model.ringB.rotation.y = elapsed * -0.25;
		sceneState.model.ringC.rotation.x = elapsed * 0.2;
	} else if (sceneState.type === 'galaxy') {
		sceneState.model.knot.rotation.x = elapsed * 0.3;
		sceneState.model.knot.rotation.y = elapsed * 0.55;
		sceneState.model.shell.rotation.z = elapsed * 0.22;
	} else if (sceneState.type === 'crystal') {
		sceneState.model.crystal.rotation.x = elapsed * 0.3;
		sceneState.model.crystal.rotation.y = elapsed * 0.4;
		sceneState.model.frame.rotation.y = elapsed * -0.2;
	} else {
		sceneState.model.core.rotation.x = elapsed * 0.25;
		sceneState.model.core.rotation.y = elapsed * 0.45;
		sceneState.model.ring.rotation.z = elapsed * 0.22;
		sceneState.model.ringTwo.rotation.y = elapsed * -0.16;
	}

	sceneState.group.rotation.y += (sceneState.pointer.x * 0.7 - sceneState.group.rotation.y) * 0.04;
	sceneState.group.rotation.x += (-sceneState.pointer.y * 0.6 - sceneState.group.rotation.x) * 0.04;

	sceneState.renderer.render(sceneState.scene, sceneState.camera);
}

function initializeTiltCards() {
	const cards = document.querySelectorAll('[data-tilt-card]');

	if (!cards.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	cards.forEach((card) => {
		const glare = card.querySelector('[data-tilt-glare]');

		card.addEventListener('mousemove', (event) => {
			const rect = card.getBoundingClientRect();
			const x = event.clientX - rect.left;
			const y = event.clientY - rect.top;
			const rotateX = ((y / rect.height) - 0.5) * -8;
			const rotateY = ((x / rect.width) - 0.5) * 12;

			card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;

			if (glare) {
				glare.style.opacity = '1';
				glare.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.5), rgba(255,255,255,0) 45%)`;
			}
		});

		card.addEventListener('mouseleave', () => {
			card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';

			if (glare) {
				glare.style.opacity = '0';
			}
		});
	});
}
