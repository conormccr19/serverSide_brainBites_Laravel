import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
	initializeHeroScene();
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

async function initializeHeroScene() {
	const canvas = document.getElementById('brainbites-hero-canvas');

	if (!canvas || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	const THREE = await import('three');

	const scene = new THREE.Scene();
	const camera = new THREE.PerspectiveCamera(42, 1, 0.1, 100);
	camera.position.set(0, 0, 7);

	const renderer = new THREE.WebGLRenderer({
		canvas,
		alpha: true,
		antialias: true,
	});
	renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

	const orbitGroup = new THREE.Group();
	scene.add(orbitGroup);

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
	orbitGroup.add(core);

	const ring = new THREE.Mesh(
		new THREE.TorusGeometry(2.1, 0.08, 16, 120),
		new THREE.MeshBasicMaterial({ color: 0xff7f3f, transparent: true, opacity: 0.7 })
	);
	ring.rotation.x = 1.2;
	ring.rotation.y = 0.5;
	orbitGroup.add(ring);

	const ringTwo = new THREE.Mesh(
		new THREE.TorusGeometry(2.8, 0.05, 16, 120),
		new THREE.MeshBasicMaterial({ color: 0xa8ff72, transparent: true, opacity: 0.35 })
	);
	ringTwo.rotation.x = 0.4;
	ringTwo.rotation.z = 0.5;
	orbitGroup.add(ringTwo);

	const particlesGeometry = new THREE.BufferGeometry();
	const particleCount = 900;
	const particlePositions = new Float32Array(particleCount * 3);

	for (let i = 0; i < particleCount; i += 1) {
		const radius = 9 * Math.sqrt(Math.random());
		const theta = Math.random() * Math.PI * 2;
		const phi = Math.acos((Math.random() * 2) - 1);

		particlePositions[i * 3] = radius * Math.sin(phi) * Math.cos(theta);
		particlePositions[(i * 3) + 1] = radius * Math.sin(phi) * Math.sin(theta);
		particlePositions[(i * 3) + 2] = radius * Math.cos(phi);
	}

	particlesGeometry.setAttribute('position', new THREE.BufferAttribute(particlePositions, 3));
	const particles = new THREE.Points(
		particlesGeometry,
		new THREE.PointsMaterial({
			color: 0xffffff,
			size: 0.03,
			transparent: true,
			opacity: 0.75,
		})
	);
	scene.add(particles);

	scene.add(new THREE.AmbientLight(0xb2faff, 0.8));
	const keyLight = new THREE.DirectionalLight(0xffffff, 1.1);
	keyLight.position.set(3, 4, 6);
	scene.add(keyLight);

	const rimLight = new THREE.PointLight(0xff8f5d, 1.2, 30);
	rimLight.position.set(-5, -2, 2);
	scene.add(rimLight);

	let pointerX = 0;
	let pointerY = 0;
	const container = canvas.closest('[data-hero-visual]');

	if (container) {
		container.addEventListener('pointermove', (event) => {
			const rect = container.getBoundingClientRect();
			pointerX = ((event.clientX - rect.left) / rect.width) - 0.5;
			pointerY = ((event.clientY - rect.top) / rect.height) - 0.5;
		});

		container.addEventListener('pointerleave', () => {
			pointerX = 0;
			pointerY = 0;
		});
	}

	const resizeRenderer = () => {
		const width = canvas.clientWidth;
		const height = canvas.clientHeight;

		if (!width || !height) {
			return;
		}

		renderer.setSize(width, height, false);
		camera.aspect = width / height;
		camera.updateProjectionMatrix();
	};

	resizeRenderer();
	window.addEventListener('resize', resizeRenderer);

	const clock = new THREE.Clock();

	const render = () => {
		const elapsed = clock.getElapsedTime();
		core.rotation.x = elapsed * 0.25;
		core.rotation.y = elapsed * 0.45;
		ring.rotation.z = elapsed * 0.22;
		ringTwo.rotation.y = elapsed * -0.16;
		particles.rotation.y = elapsed * 0.035;

		orbitGroup.rotation.y += (pointerX * 0.7 - orbitGroup.rotation.y) * 0.04;
		orbitGroup.rotation.x += (-pointerY * 0.6 - orbitGroup.rotation.x) * 0.04;

		renderer.render(scene, camera);
		window.requestAnimationFrame(render);
	};

	render();
}
