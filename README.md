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

Brief description of the project and its purpose.

## Monorepo Structure

- `wp-content/themes/ourblocktheme` — Custom WordPress theme
- `wp-content/plugins/interactivity-quiz` — Custom WordPress plugin

## Requirements

- Node.js (10.9.2 or higher)
- NVM (version 23.8.0 or higher)
- Yarn (v4.9.1)
- PHP (for WordPress)
- WordPress installation

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

- yarn format — Format codebase
- yarn lint:css — Lint CSS files
- yarn lint:js — Lint JS files
- yarn packages-update — Update packages

ourblocktheme scripts (wp-content/themes/ourblocktheme/package.json)

- yarn start — Start development server for theme
- yarn blocks — Start block development with experimental modules
- yarn build — Build theme assets
- yarn format — Format theme code
- yarn lint:css — Lint theme CSS
- yarn lint:js — Lint theme JS
- yarn packages-update — Update theme packages

### Troubleshooting

Ensure all dependencies are installed with the correct versions.
Check for errors in the terminal and resolve missing packages.
