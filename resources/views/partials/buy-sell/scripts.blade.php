@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            // Define the global Alpine Store for the Zoom Modal
            Alpine.store('zoom', {
                isOpen: false,
                url: '',
                index: 0,
                scale: 1,
                panX: 0,
                panY: 0,

                open(imageUrl, imageIndex) {
                    this.url = imageUrl;
                    this.index = imageIndex;
                    this.scale = 1;
                    this.panX = 0;
                    this.panY = 0;
                    this.isOpen = true;
                    // Prevent background scrolling when modal is open
                    document.body.style.overflow = 'hidden';
                },

                close() {
                    this.isOpen = false;
                    document.body.style.overflow = '';
                },

                zoomIn() {
                    // Maximum zoom level of 3x
                    this.scale = Math.min(this.scale + 0.5, 3);
                },

                zoomOut() {
                    // Minimum zoom level of 1x
                    this.scale = Math.max(this.scale - 0.5, 1);
                    // Reset pan position if zoomed out to 1x
                    if (this.scale === 1) {
                        this.panX = 0;
                        this.panY = 0;
                    }
                }
            });

            // Define the Alpine Component for Zoom/Pan functionality
            Alpine.data('imageZoom', () => ({
                isPanning: false,
                panStartX: 0,
                panStartY: 0,

                // Helper to reset the image to the center of the viewport
                centerImage() {
                    Alpine.store('zoom').panX = 0;
                    Alpine.store('zoom').panY = 0;
                },

                // Mouse down handler to start panning
                startPan(e) {
                    if (Alpine.store('zoom').scale > 1) {
                        this.isPanning = true;
                        this.panStartX = e.clientX;
                        this.panStartY = e.clientY;
                    }
                },

                // Mouse move handler for dragging
                pan(e) {
                    if (!this.isPanning) return;

                    const dx = e.clientX - this.panStartX;
                    const dy = e.clientY - this.panStartY;

                    Alpine.store('zoom').panX += dx;
                    Alpine.store('zoom').panY += dy;

                    this.panStartX = e.clientX;
                    this.panStartY = e.clientY;
                },

                // Mouse up/leave handler to stop panning
                endPan() {
                    this.isPanning = false;
                },

                // Mouse wheel handler for zoom control
                zoomWheel(e) {
                    const zoomStore = Alpine.store('zoom');

                    if (e.deltaY < 0) {
                        zoomStore.zoomIn();
                    } else {
                        zoomStore.zoomOut();
                    }

                    // Use nextTick to ensure pan reset happens immediately after zoom out
                    this.$nextTick(() => {
                        if (zoomStore.scale === 1) {
                            this.centerImage();
                        }
                    });
                }
            }));
        });
    </script>
@endpush
