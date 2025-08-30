// Notes Display Fix
// Add this to your Vue.js application to ensure notes display correctly

// Fix 1: Ensure user authentication is working
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Notes Display Fix Applied');

    // Fix 2: Check and fix localStorage user data
    const userData = localStorage.getItem('user');
    if (userData) {
        try {
            const user = JSON.parse(userData);
            console.log('Current user data:', user);

            if (!user.id) {
                console.error('❌ User ID missing from localStorage');
                // Try to get user data from API
                fetchUserData();
            } else {
                console.log('✅ User ID found:', user.id);
            }
        } catch (error) {
            console.error('Error parsing user data:', error);
            fetchUserData();
        }
    } else {
        console.log('No user data in localStorage');
        fetchUserData();
    }

    // Fix 3: Test notes API manually
    setTimeout(() => {
        testNotesAPI();
    }, 1000);
});

// Fix 4: Fetch user data from API
async function fetchUserData() {
    try {
        console.log('Fetching user data from API...');
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
                console.log('✅ User data saved to localStorage:', data.user);
            }
        }
    } catch (error) {
        console.error('Error fetching user data:', error);
    }
}

// Fix 5: Test notes API
async function testNotesAPI() {
    const userData = localStorage.getItem('user');
    if (!userData) {
        console.error('❌ No user data available for notes API test');
        return;
    }

    try {
        const user = JSON.parse(userData);
        console.log('Testing notes API for user:', user.id);

        const response = await fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-User-ID': user.id
            }
        });

        if (response.ok) {
            const data = await response.json();
            console.log('✅ Notes API test result:', data);

            if (data.success && data.data) {
                console.log('✅ Notes API working! Found', data.data.length, 'notes');
            } else {
                console.error('❌ Notes API returned error:', data.error);
            }
        } else {
            console.error('❌ Notes API request failed:', response.status);
        }
    } catch (error) {
        console.error('Error testing notes API:', error);
    }
}

// Fix 6: Vue.js component debugging
function debugNotesComponent() {
    // This function can be called from Vue components to debug notes loading
    console.log('=== NOTES COMPONENT DEBUG ===');

    // Check if Vue is available
    if (typeof Vue === 'undefined') {
        console.error('❌ Vue.js not loaded');
        return;
    }

    // Check if we're on the notes page
    if (window.location.pathname.includes('/notes')) {
        console.log('✅ On notes page');

        // Check for Vue instances
        const vueInstances = document.querySelectorAll('[data-v-]');
        console.log('Vue instances found:', vueInstances.length);

        // Check for notes container
        const notesContainer = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3');
        if (notesContainer) {
            console.log('✅ Notes container found');
            console.log('Notes container children:', notesContainer.children.length);
        } else {
            console.log('❌ Notes container not found');
        }
    } else {
        console.log('Not on notes page');
    }
}

// Fix 7: Force refresh notes
function forceRefreshNotes() {
    console.log('🔄 Forcing notes refresh...');

    // Clear any cached data
    if (localStorage.getItem('notes_cache')) {
        localStorage.removeItem('notes_cache');
    }

    // Reload the page
    window.location.reload();
}

// Fix 8: Make functions globally available
window.debugNotesComponent = debugNotesComponent;
window.forceRefreshNotes = forceRefreshNotes;
window.testNotesAPI = testNotesAPI;

console.log('🔧 Notes Display Fix Loaded');
console.log('Available functions:');
console.log('- debugNotesComponent()');
console.log('- forceRefreshNotes()');
console.log('- testNotesAPI()');