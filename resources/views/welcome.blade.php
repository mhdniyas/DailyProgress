<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futuristic Multiplanetary Station</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Three.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <style>
        body { margin: 0; overflow: hidden; }
        #canvas { width: 100vw; height: 100vh; position: absolute; top: 0; left: 0; z-index: 0; }
        .content { position: relative; z-index: 10; }
    </style>
</head>
<body class="bg-gray-900 text-white font-sans">
    <!-- Three.js Canvas -->
    <canvas id="canvas"></canvas>

    <!-- Content -->
    <div class="content min-h-screen flex flex-col justify-center items-center">
        <h1 class="text-5xl md:text-7xl font-bold tracking-wider text-cyan-400 animate-pulse">
            Interstellar Hub
        </h1>
        <p class="mt-4 text-lg md:text-2xl text-gray-300 max-w-2xl text-center">
            Welcome to the gateway of multiplanetary exploration. Launch into the future.
        </p>
        <button class="mt-8 px-6 py-3 bg-cyan-500 text-gray-900 font-semibold rounded-full hover:bg-cyan-400 transition duration-300">
            Explore Now
        </button>
    </div>

    <!-- Three.js Animation Script -->
    <script>
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById('canvas'), alpha: true });
        renderer.setSize(window.innerWidth, window.innerHeight);

        // Create a simple rotating sphere (planet/station)
        const geometry = new THREE.SphereGeometry(1, 32, 32);
        const material = new THREE.MeshBasicMaterial({ color: 0x00ffff, wireframe: true });
        const sphere = new THREE.Mesh(geometry, material);
        scene.add(sphere);

        camera.position.z = 5;

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);
            sphere.rotation.x += 0.01;
            sphere.rotation.y += 0.01;
            renderer.render(scene, camera);
        }
        animate();

        // Resize handling
        window.addEventListener('resize', () => {
            renderer.setSize(window.innerWidth, window.innerHeight);
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
        });
    </script>
</body>
</html>
