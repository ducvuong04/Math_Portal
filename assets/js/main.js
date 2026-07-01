// MathPortal 12 - Main JS
document.addEventListener('DOMContentLoaded', () => {
    console.log('MathPortal 12 Loaded');
    
    // Add intersection observer for animations
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.8s ease-out';
        observer.observe(el);
    });

    // Theme Switch Logic
    const toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');
    const currentTheme = localStorage.getItem('theme');

    if (currentTheme) {
        document.documentElement.setAttribute('data-theme', currentTheme);
        if (currentTheme === 'light') {
            toggleSwitch.checked = true;
        }
    }

    function switchTheme(e) {
        if (e.target.checked) {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        }
    }

    if (toggleSwitch) {
        toggleSwitch.addEventListener('change', switchTheme, false);
    }

    // Mobile Navigation Drawer Toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const navLinks = document.getElementById('nav-links');
    const iconMenu = document.querySelector('.icon-menu');
    const iconClose = document.querySelector('.icon-close');

    if (mobileMenuToggle && navLinks) {
        mobileMenuToggle.addEventListener('click', () => {
            const isOpen = navLinks.classList.toggle('active');
            
            // Toggle icons
            if (isOpen) {
                iconMenu.style.display = 'none';
                iconClose.style.display = 'block';
                document.body.style.overflow = 'hidden'; // prevent scrolling behind the menu drawer
            } else {
                iconMenu.style.display = 'block';
                iconClose.style.display = 'none';
                document.body.style.overflow = '';
            }
        });

        // Close menu when clicking on a link (useful if linking to section on same page)
        navLinks.querySelectorAll('a:not(.dropdown-toggle)').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                iconMenu.style.display = 'block';
                iconClose.style.display = 'none';
                document.body.style.overflow = '';
            });
        });
    }

    // Mobile Dropdowns Toggle
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                e.stopPropagation();
                
                const parentLi = toggle.closest('.has-dropdown');
                const dropdownMenu = parentLi.querySelector('.dropdown-menu');
                
                // Toggle active/open class
                const isAlreadyOpen = parentLi.classList.contains('open');
                
                // Close other main dropdowns
                document.querySelectorAll('.has-dropdown').forEach(li => {
                    if (li !== parentLi && !li.contains(parentLi)) {
                        li.classList.remove('open');
                    }
                });
                
                if (isAlreadyOpen) {
                    parentLi.classList.remove('open');
                } else {
                    parentLi.classList.add('open');
                }
            }
        });
    });

    // Handle submenu dropdowns inside dropdowns on mobile
    const submenuLinks = document.querySelectorAll('.has-submenu > a');
    submenuLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                e.stopPropagation();
                
                const parentSub = link.closest('.has-submenu');
                const isOpen = parentSub.classList.contains('open');
                
                // Close other submenus in same level
                const parentMenu = parentSub.closest('.dropdown-menu, .submenu');
                parentMenu.querySelectorAll('.has-submenu').forEach(sub => {
                    if (sub !== parentSub) {
                        sub.classList.remove('open');
                    }
                });
                
                if (isOpen) {
                    parentSub.classList.remove('open');
                } else {
                    parentSub.classList.add('open');
                }
            }
        });
    });
});
