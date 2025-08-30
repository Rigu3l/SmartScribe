// Notes Authentication Fix
// Add this to your Vue.js application to ensure notes display correctly

// Fix 1: Ensure user authentication works properly
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Notes Authentication Fix Applied');

    // Fix 2: Check and fix user authentication
    const userData = localStorage.getItem('user');
    const token = localStorage.getItem('token');

    if (!userData) {
        console.error('❌ No user data in localStorage');
        // Try to redirect to login
        if (window.location.pathname !== '/login' && window.location.pathname !== '/') {
            console.log('Redirecting to login...');
            window.location.href = '/login';
        }
        return;
    }

    if (!token) {
        console.warn('⚠️ No token in localStorage');
    }

    try {
        const user = JSON.parse(userData);
        console.log('✅ User data loaded:', user);

        if (!user.id) {
            console.error('❌ User ID missing from user data');
            // Try to fetch user data from API
            fetchUserProfile();
            return;
        }

        // Fix 3: Test API authentication
        testAPIAuthentication(user.id);

    } catch (error) {
        console.error('❌ Error parsing user data:', error);
        localStorage.removeItem('user');
        localStorage.removeItem('token');
        window.location.href = '/login';
    }
});

// Fix 4: Fetch user profile from API
async function fetchUserProfile() {
    try {
        console.log('Fetching user profile from API...');
        const response = await fetch('http://localhost/SmartScribe-main/public/index.php?resource=auth&action=profile', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success && data.user) {
                localStorage.setItem('user', JSON.stringify(data.user));
                console.log('✅ User profile saved to localStorage:', data.user);

                // Reload the page to apply changes
                window.location.reload();
            } else {
                console.error('❌ API returned error:', data.error);
            }
        } else {
            console.error('❌ Failed to fetch user profile:', response.status);
        }
    } catch (error) {
        console.error('❌ Error fetching user profile:', error);
    }
}

// Fix 5: Test API authentication
async function testAPIAuthentication(userId) {
    try {
        console.log('Testing API authentication for user:', userId);

        const response = await fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-User-ID': userId
            }
        });

        if (response.ok) {
            const data = await response.json();
            console.log('✅ API authentication test result:', data);

            if (data.success) {
                console.log('✅ API authentication working! Found', data.data ? data.data.length : 0, 'notes');
            } else {
                console.error('❌ API authentication failed:', data.error);
            }
        } else {
            console.error('❌ API authentication request failed:', response.status);
        }
    } catch (error) {
        console.error('❌ Error testing API authentication:', error);
    }
}

// Fix 6: Vue.js interceptor for API requests
if (typeof window !== 'undefined' && window.Vue) {
    // Add global API interceptor
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        // Add authentication headers to all API requests
        if (url.includes('/SmartScribe-main/public/index.php')) {
            const userData = localStorage.getItem('user');
            if (userData) {
                try {
                    const user = JSON.parse(userData);
                    if (user.id) {
                        options.headers = options.headers || {};
                        options.headers['X-User-ID'] = user.id;
                        options.headers['Content-Type'] = options.headers['Content-Type'] || 'application/json';
                        console.log('🔧 Added X-User-ID header:', user.id);
                    }
                } catch (error) {
                    console.error('Error adding auth headers:', error);
                }
            }
        }

        return originalFetch.call(this, url, options);
    };
}

// Fix 7: Force authentication check
function checkAuthentication() {
    const userData = localStorage.getItem('user');
    const token = localStorage.getItem('token');

    if (!userData || !token) {
        console.error('❌ Authentication failed - missing user data or token');
        return false;
    }

    try {
        const user = JSON.parse(userData);
        if (!user.id) {
            console.error('❌ Authentication failed - missing user ID');
            return false;
        }

        console.log('✅ Authentication check passed for user:', user.id);
        return true;
    } catch (error) {
        console.error('❌ Authentication failed - error parsing user data:', error);
        return false;
    }
}

// Fix 8: Force reload notes
function forceReloadNotes() {
    console.log('🔄 Forcing notes reload...');

    // Clear any cached data
    if (localStorage.getItem('notes_cache')) {
        localStorage.removeItem('notes_cache');
    }

    // Check authentication
    if (!checkAuthentication()) {
        console.error('Cannot reload notes - authentication failed');
        return;
    }

    // Reload the page
    window.location.reload();
}

// Fix 9: Debug Vue.js components
function debugVueComponents() {
    console.log('=== VUE.JS COMPONENTS DEBUG ===');

    // Check if Vue is loaded
    if (typeof Vue === 'undefined') {
        console.error('❌ Vue.js not loaded');
        return;
    }

    // Check for Vue instances
    const allElements = document.querySelectorAll('*');
    let vueInstances = 0;

    allElements.forEach(el => {
        if (el.__vue__) {
            vueInstances++;
        }
    });

    console.log('Vue instances found:', vueInstances);

    // Check for notes-specific elements
    const notesContainer = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3');
    if (notesContainer) {
        console.log('✅ Notes container found');
        console.log('Notes container children:', notesContainer.children.length);

        // Check if there are any note cards
        const noteCards = notesContainer.querySelectorAll('.bg-gray-800.rounded-lg');
        console.log('Note cards found:', noteCards.length);

        if (noteCards.length === 0) {
            console.warn('⚠️ No note cards found - this might indicate an issue with data loading');
        }
    } else {
        console.error('❌ Notes container not found');
    }

    // Check for loading indicators
    const loadingElements = document.querySelectorAll('.animate-spin');
    console.log('Loading indicators found:', loadingElements.length);

    // Check for error messages
    const errorElements = document.querySelectorAll('.bg-red-800');
    console.log('Error messages found:', errorElements.length);
}

// Fix 10: Make functions globally available
window.checkAuthentication = checkAuthentication;
window.forceReloadNotes = forceReloadNotes;
window.debugVueComponents = debugVueComponents;
window.fetchUserProfile = fetchUserProfile;
window.testAPIAuthentication = testAPIAuthentication;

console.log('🔧 Notes Authentication Fix Loaded');
console.log('Available functions:');
console.log('- checkAuthentication()');
console.log('- forceReloadNotes()');
console.log('- debugVueComponents()');
console.log('- fetchUserProfile()');
console.log('- testAPIAuthentication()');