import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

const TOUR_KEY = 'whiziq_tour_v1_done';

function findNavGroup(name) {
    const groups = document.querySelectorAll('.fi-sidebar-group');
    for (const g of groups) {
        const btn = g.querySelector('button');
        if (btn && btn.textContent.trim().toLowerCase().includes(name.toLowerCase())) {
            return g;
        }
    }
    return null;
}

function findNavItem(label) {
    const links = document.querySelectorAll('.fi-sidebar-item a, .fi-sidebar-item button');
    for (const el of links) {
        if (el.textContent.trim().toLowerCase().includes(label.toLowerCase())) {
            return el.closest('.fi-sidebar-item') ?? el;
        }
    }
    return null;
}

function buildSteps() {
    const steps = [
        {
            popover: {
                title: '👋 Welcome to WhizIQ!',
                description: "Let's take a quick tour so you know where everything is. You can skip at any time.",
                side: 'over',
                align: 'center',
            },
        },
    ];

    const sidebar = document.querySelector('.fi-sidebar');
    if (sidebar) {
        steps.push({
            element: sidebar,
            popover: {
                title: '🗂 Your Navigation',
                description: 'Everything you need is in this sidebar, organised into groups. Click any group to expand it.',
                side: 'right',
                align: 'start',
            },
        });
    }

    const moneyGroup = findNavGroup('Money');
    if (moneyGroup) {
        steps.push({
            element: moneyGroup,
            popover: {
                title: '💰 Money',
                description: 'Create invoices and receipts, track expenses, log revenue, and manage your finances all in one place.',
                side: 'right',
                align: 'start',
            },
        });
    }

    const clientsGroup = findNavGroup('Clients');
    if (clientsGroup) {
        steps.push({
            element: clientsGroup,
            popover: {
                title: '👥 Clients',
                description: 'Store and manage your contacts, segment them, and keep track of deals in your pipeline.',
                side: 'right',
                align: 'start',
            },
        });
    }

    const workGroup = findNavGroup('Work');
    if (workGroup) {
        steps.push({
            element: workGroup,
            popover: {
                title: '✅ Work',
                description: 'Manage tasks, set goals, schedule appointments, and track your productivity in one place.',
                side: 'right',
                align: 'start',
            },
        });
    }

    const analyticsGroup = findNavGroup('Analytics');
    if (analyticsGroup) {
        steps.push({
            element: analyticsGroup,
            popover: {
                title: '📊 Analytics',
                description: 'Get deep insights into your business performance, revenue forecasts, and marketing results.',
                side: 'right',
                align: 'start',
            },
        });
    }

    const marketingGroup = findNavGroup('Marketing');
    if (marketingGroup) {
        steps.push({
            element: marketingGroup,
            popover: {
                title: '📣 Marketing',
                description: 'Send emails to clients, manage aftercare sequences, and track your marketing campaigns.',
                side: 'right',
                align: 'start',
            },
        });
    }

    const settingsGroup = findNavGroup('Settings');
    if (settingsGroup) {
        steps.push({
            element: settingsGroup,
            popover: {
                title: '⚙️ Settings',
                description: 'Configure your business profile, notifications, booking settings, and more.',
                side: 'right',
                align: 'start',
            },
        });
    }

    const topbar = document.querySelector('.fi-topbar, header.fi-header, .fi-layout-sidebar-header');
    if (topbar) {
        steps.push({
            element: topbar,
            popover: {
                title: '🔔 Top Bar',
                description: 'Access your profile, notifications, and quick settings from here.',
                side: 'bottom',
                align: 'end',
            },
        });
    }

    steps.push({
        popover: {
            title: "🚀 You're all set!",
            description: "That's the grand tour! Dive in and explore. You can always restart this tour from the Help menu.",
            side: 'over',
            align: 'center',
        },
    });

    return steps;
}

function startTour(force = false) {
    if (!force && localStorage.getItem(TOUR_KEY)) return;

    const driverObj = driver({
        showProgress: true,
        animate: true,
        overlayOpacity: 0.55,
        smoothScroll: true,
        allowClose: true,
        doneBtnText: 'Done',
        closeBtnText: 'Skip',
        nextBtnText: 'Next →',
        prevBtnText: '← Back',
        onDestroyed: () => {
            localStorage.setItem(TOUR_KEY, '1');
        },
        steps: buildSteps(),
    });

    driverObj.drive();
}

// Expose globally so a "Restart Tour" button can call window.startWhizIQTour()
window.startWhizIQTour = () => startTour(true);

// Auto-start on first visit — wait for Filament/Livewire to fully render
document.addEventListener('livewire:navigated', () => startTour(), { once: true });
document.addEventListener('DOMContentLoaded', () => {
    // Fallback if livewire:navigated doesn't fire
    setTimeout(() => startTour(), 1500);
});
