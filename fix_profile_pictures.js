// Profile Picture Display Fix
// Add this to your Vue.js application to ensure profile pictures display correctly

// Fix 1: Ensure user data is loaded on app initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Profile Picture Fix Applied');

    // Fix 2: Check and fix localStorage user data
    const userData = localStorage.getItem('user');
    if (userData) {
        try {
            const user = JSON.parse(userData);
            console.log('Current user data:', user);

            // Fix 3: Ensure profile picture path is correct
            if (user.profilePicture) {
                console.log('Profile picture path:', user.profilePicture);

                // Fix 4: Test image accessibility
                const img = new Image();
                const imageUrl = `/${user.profilePicture}?t=${Date.now()}`;
                img.src = imageUrl;

                img.onload = function() {
                    console.log('✅ Profile picture is accessible:', imageUrl);
                };

                img.onerror = function() {
                    console.error('❌ Profile picture not accessible:', imageUrl);

                    // Fix 5: Try alternative path
                    const altUrl = `/SmartScribe-main/public/${user.profilePicture}?t=${Date.now()}`;
                    console.log('Trying alternative URL:', altUrl);

                    const altImg = new Image();
                    altImg.src = altUrl;
                    altImg.onload = function() {
                        console.log('✅ Alternative URL works:', altUrl);
                    };
                    altImg.onerror = function() {
                        console.error('❌ Alternative URL also failed');
                    };
                };
            } else {
                console.log('No profile picture set for user');
            }
        } catch (error) {
            console.error('Error parsing user data:', error);
        }
    } else {
        console.log('No user data in localStorage');
    }
});

// Fix 6: Vue.js component fix for profile picture display
function fixProfilePictureDisplay() {
    // This function can be called from Vue components to ensure profile pictures display
    const profileImages = document.querySelectorAll('img[alt="Profile"]');

    profileImages.forEach(img => {
        if (img.src.includes('localhost:3000')) {
            // Fix incorrect URL
            const newSrc = img.src.replace('localhost:3000', 'localhost/SmartScribe-main/public');
            img.src = newSrc;
            console.log('Fixed profile picture URL:', newSrc);
        }

        // Add error handling
        img.onerror = function() {
            console.error('Profile picture failed to load:', img.src);
            // Try alternative path
            if (!img.src.includes('/SmartScribe-main/public/')) {
                img.src = img.src.replace('/uploads/', '/SmartScribe-main/public/uploads/');
            }
        };
    });
}

// Fix 7: Make function globally available
window.fixProfilePictureDisplay = fixProfilePictureDisplay;

// Fix 8: Auto-run fix when Vue components mount
const originalCreateApp = Vue?.createApp;
if (originalCreateApp) {
    Vue.createApp = function(...args) {
        const app = originalCreateApp.apply(this, args);

        // Add global mixin to fix profile pictures
        app.mixin({
            mounted() {
                // Small delay to ensure DOM is ready
                setTimeout(() => {
                    fixProfilePictureDisplay();
                }, 100);
            }
        });

        return app;
    };
}

console.log('🔧 Profile Picture Fix Loaded - Check console for debug info');