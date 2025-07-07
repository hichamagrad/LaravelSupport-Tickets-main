document.addEventListener('alpine:init', () => {
    Alpine.data('data', () => ({
        isProfileMenuOpen: false,
        toggleProfileMenu() {
            this.isProfileMenuOpen = !this.isProfileMenuOpen
            
            // Close dropdown when clicking outside
            if (this.isProfileMenuOpen) {
                document.addEventListener('click', this.outsideClickListener = (event) => {
                    const dropdown = document.querySelector('[x-show="isProfileMenuOpen"]');
                    const button = document.querySelector('[aria-haspopup="true"]');
                    
                    if (dropdown && !dropdown.contains(event.target) && button && !button.contains(event.target)) {
                        this.isProfileMenuOpen = false;
                        document.removeEventListener('click', this.outsideClickListener);
                    }
                });
            }
        },

        closeProfileMenu() {
            this.isProfileMenuOpen = false
        },

        isSideMenuOpen: false,
        toggleSideMenu() {
            this.isSideMenuOpen = !this.isSideMenuOpen
        },

        closeSideMenu() {
            this.isSideMenuOpen = false
        },

        isMultiLevelMenuOpen: false,
        toggleMultiLevelMenu() {
            this.isMultiLevelMenuOpen = !this.isMultiLevelMenuOpen
        }
    }))
})
