# Tiny Tiny RSS Linkding Plugin

## Description

This is an open source plugin for Tiny Tiny RSS which allows you to save articles to Linkding with a single click or hotkey.

## Table of Contents

* [Features](#features)
* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
* [Credits](#credits)

## Features

* Save articles to Linkding with a single click
* Hotkey support (press 'l' to save current article)
* Checks if article is already bookmarked before creating a new bookmark
* Simple token-based authentication

## Installation

Clone this repository to your `plugins.local` folder of your Tiny Tiny RSS installation.

```
$ cd tt-rss/plugins.local
$ git clone https://github.com/black-roland/ttrss-linkding linkding
```

Alternatively, you can download the ZIP file and extract it to your `plugins.local` folder, ensuring the folder is named `linkding`.

Enable the `linkding` plugin in the Tiny Tiny RSS Preferences and reload.

## Configuration

After enabling the plugin, a Linkding configuration pane will appear in your Tiny Tiny RSS preferences. You need to configure the following settings:

1.  **Linkding URL**
    Enter your Linkding instance URL (e.g., `https://linkding.example.com`).

2.  **Linkding REST API Token**
    Find your API token in Linkding under **Settings → Integrations**.

### To obtain your Linkding API token:

1.  Log into your Linkding instance.
2.  Go to **Settings → Integrations**.
3.  Find the "REST API" section.
4.  Copy the API token shown there.
5.  Paste it into the "Linkding REST API Token" field in the Tiny Tiny RSS plugin settings.

## Usage

After configuring the plugin, you'll see a Linkding icon next to each article. Click the icon to save the article to Linkding.

You can also use the hotkey 'l' to save the currently selected article to Linkding.

The plugin will automatically check if an article is already bookmarked in Linkding to prevent duplicates.

## Credits

This plugin was originally inspired by the [oneclickpocket](https://github.com/fxneumann/oneclickpocket) plugin by fxneumann. The original code did not specify a license and has been extensively rewritten and refactored. If you are the original author and have any concerns, please contact me.
