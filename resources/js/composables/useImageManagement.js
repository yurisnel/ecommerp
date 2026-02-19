/**
 * Image Management Utilities for Vue 3
 * Handles image compression, cropping, and uploading
 */

export const useImageManagement = () => {
    /**
     * Compress image file before upload
     */
    const compressImageFile = async (file, quality = 0.85) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = (event) => {
                const img = new Image();

                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    // Set canvas dimensions
                    canvas.width = img.width;
                    canvas.height = img.height;

                    // Draw and compress
                    ctx.drawImage(img, 0, 0);
                    canvas.toBlob(
                        (blob) => {
                            if (blob) {
                                resolve(new File([blob], file.name, { type: 'image/jpeg' }));
                            } else {
                                reject(new Error('Compression failed'));
                            }
                        },
                        'image/jpeg',
                        quality
                    );
                };

                img.onerror = () => reject(new Error('Could not load image'));
                img.src = event.target.result;
            };

            reader.onerror = () => reject(new Error('Could not read file'));
            reader.readAsDataURL(file);
        });
    };

    /**
     * Get image dimensions
     */
    const getImageDimensions = (file) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = (event) => {
                const img = new Image();

                img.onload = () => {
                    resolve({
                        width: img.width,
                        height: img.height,
                        size: file.size,
                        type: file.type
                    });
                };

                img.onerror = () => reject(new Error('Could not load image'));
                img.src = event.target.result;
            };

            reader.onerror = () => reject(new Error('Could not read file'));
            reader.readAsDataURL(file);
        });
    };

    /**
     * Resize image to fit within max dimensions
     */
    const resizeImage = async (file, maxWidth = 2000, maxHeight = 2000) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = (event) => {
                const img = new Image();

                img.onload = () => {
                    let width = img.width;
                    let height = img.height;

                    // Calculate new dimensions
                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(
                        (blob) => {
                            if (blob) {
                                resolve(new File([blob], file.name, { type: file.type }));
                            } else {
                                reject(new Error('Resize failed'));
                            }
                        },
                        file.type,
                        0.95
                    );
                };

                img.onerror = () => reject(new Error('Could not load image'));
                img.src = event.target.result;
            };

            reader.onerror = () => reject(new Error('Could not read file'));
            reader.readAsDataURL(file);
        });
    };

    /**
     * Get file size in MB
     */
    const getFileSizeMB = (size) => {
        return (size / (1024 * 1024)).toFixed(2);
    };

    /**
     * Validate image file
     */
    const validateImageFile = (file) => {
        const errors = [];
        const maxSize = 2 * 1024 * 1024; // 2MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!allowedTypes.includes(file.type)) {
            errors.push(`Invalid file type. Allowed: ${allowedTypes.join(', ')}`);
        }

        if (file.size > maxSize) {
            errors.push(`File size (${getFileSizeMB(file.size)}MB) exceeds 2MB limit`);
        }

        return {
            valid: errors.length === 0,
            errors
        };
    };

    /**
     * Convert blob URL to File
     */
    const blobToFile = async (blobUrl, filename) => {
        const response = await fetch(blobUrl);
        const blob = await response.blob();
        return new File([blob], filename, { type: blob.type });
    };

    /**
     * Create thumbnail
     */
    const createThumbnail = async (file, width = 150, height = 150) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = (event) => {
                const img = new Image();

                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(
                        (blob) => {
                            if (blob) {
                                resolve(blob);
                            } else {
                                reject(new Error('Thumbnail creation failed'));
                            }
                        },
                        'image/jpeg',
                        0.9
                    );
                };

                img.onerror = () => reject(new Error('Could not load image'));
                img.src = event.target.result;
            };

            reader.onerror = () => reject(new Error('Could not read file'));
            reader.readAsDataURL(file);
        });
    };

    return {
        compressImageFile,
        getImageDimensions,
        resizeImage,
        getFileSizeMB,
        validateImageFile,
        blobToFile,
        createThumbnail
    };
};

export default useImageManagement;
