import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';

export class FinancialVisualizer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.scene = new THREE.Scene();
        this.camera = new THREE.PerspectiveCamera(75, this.container.clientWidth / this.container.clientHeight, 0.1, 1000);
        this.renderer = new THREE.WebGLRenderer({
            antialias: true,
            alpha: true // Makes background transparent
        });

        this.init();
    }

    init() {
        // Setup renderer
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.renderer.setClearColor(0x000000, 0); // Transparent background
        this.container.appendChild(this.renderer.domElement);

        // Add padding to the scene
        const padding = 2; // Adjust this value to change the padding
        this.camera.position.z = 15 + padding;

        // Add lights
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
        this.scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.5);
        directionalLight.position.set(10, 10, 10);
        this.scene.add(directionalLight);

        // Add controls
        this.controls = new OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;

        // Handle window resize
        window.addEventListener('resize', () => this.onWindowResize(), false);
    }

    visualizeExpenses(data) {
        // Clear existing bars
        this.scene.children = this.scene.children.filter(child => !(child instanceof THREE.Mesh));

        const maxAmount = Math.max(...Object.values(data));
        const barWidth = 1;
        const spacing = 0.2;
        const categories = Object.keys(data);

        categories.forEach((category, index) => {
            const amount = data[category];
            const height = (amount / maxAmount) * 10;

            const geometry = new THREE.BoxGeometry(barWidth, height, barWidth);
            const material = new THREE.MeshPhongMaterial({
                color: new THREE.Color().setHSL(index / categories.length, 0.7, 0.5),
            });

            const bar = new THREE.Mesh(geometry, material);

            // Position the bar
            const xPos = (index - (categories.length - 1) / 2) * (barWidth + spacing);
            bar.position.set(xPos, height / 2, 0);

            this.scene.add(bar);
        });
    }

    animate() {
        requestAnimationFrame(() => this.animate());
        this.controls.update();
        this.renderer.render(this.scene, this.camera);
    }

    onWindowResize() {
        this.camera.aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
    }
}
