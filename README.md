# Project Documentation

## Table of Contents

- [Overview](#overview)
- [Monorepo Structure](#monorepo-structure)
- [Requirements](#requirements)
- [Installation](#installation)
- [Available Scripts](#available-scripts)
- [Workspace: ourblocktheme](#workspace-ourblocktheme)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## Overview

The OurBlock Theme is a custom WordPress theme designed to provide a dynamic and feature-rich experience for managing and displaying content. It integrates custom post types, advanced custom fields, and block-based layouts to create a flexible and modern website.

#### **Key Features**

Custom Post Types:
Includes Campus, Event, Program, and Professor post types, each tailored for specific content needs with custom labels, archives, and REST API support. 



Custom Blocks:
A variety of custom Gutenberg blocks are registered, such as header, footer, eventsandblogs, and more, enabling modular and reusable content creation.


Event Management:
Features an Event post type with custom queries for upcoming and past events, ensuring easy navigation and organization.


User Role Customization:
Redirects subscribers to the homepage and hides the admin bar for a streamlined user experience.


Custom Login Screen:
Styled login page with custom branding, fonts, and links.


Private Notes:
Implements a Note post type with restrictions to ensure privacy and limit the number of notes per user.


Google Maps Integration:
Supports Google Maps API for enhanced location-based features.


Modern Development Stack:
Built with PHP, JavaScript, React, Composer, and npm for a robust and scalable development workflow.

## Monorepo Structure

- `wp-content/themes/ourblocktheme` — Custom WordPress theme
- `wp-content/plugins/interactivity-quiz` — Custom WordPress plugin

## Requirements

- Yarn (v4.9.1)
- Node.js (23.8.0 or higher)
- NPM (10.9.2 or higher)
- NVM (version 23.8.0 or higher)
- Dart SASS (version 1.89.0 or higher) - Node-SASS is now deprecated
- PHP (for WordPress) Version 8.2 or higher
- WordPress installation - via WP Local

## Installation

### 1. Clone the repository

```
git clone <repository-url>
cd <project-root>
````

- Create a file names `.yarnrc.yml` in the root of the project with the following content:
  `nodeLinker: node-modules`

### 2. Install dependencies

````
yarn install
````

### 3. Install WordPress

Using [LocalWP](https://localwp.com/) or any other method, set up a WordPress installation.

### Available Scripts

- `yarn workspace outblocktheme format` — Format codebase
- `yarn workspace outblocktheme lint:css` — Lint CSS files
- `yarn workspace outblocktheme lint:js` — Lint JS files
- `yarn workspace outblocktheme packages-update` — Update packages

ourblocktheme scripts (wp-content/themes/ourblocktheme/package.json)

- `yarn workspace outblocktheme start` — Start development server for theme
- `yarn workspace outblocktheme blocks` — Start block development with experimental modules
- `yarn workspace outblocktheme build` — Build theme assets
- `yarn workspace outblocktheme format` — Format theme code
- `yarn workspace outblocktheme lint:css` — Lint theme CSS
- `yarn workspace outblocktheme lint:js` — Lint theme JS
- `yarn workspace outblocktheme packages-update` — Update theme packages

### Custom Post types 

Campus:
Title: Campuses
Description: Represents different campus locations with details like title, editor content, excerpt, thumbnail, and page attributes.


Event:
Title: Events
Description: Represents events with details like title, editor content, excerpt, thumbnail, and page attributes. Includes an archive for listing all events.


Program:
Title: Programs
Description: Represents academic programs with details like title, editor content, thumbnail, and page attributes. Includes an archive for listing all programs.


Professor:
Title: Professors
Description: Represents professors with details like title, editor content, thumbnail, and page attributes. Does not include an archive.

### Troubleshooting

Ensure all dependencies are installed with the correct versions.
Check for errors in the terminal and resolve missing packages.
