# Nolej AI for ILIAS LMS Page Component
This is the companion plugin for [Nolej AI for ILIAS LMS](https://github.com/Neuronys/ilias-robj_nolej).

Generate the activities with the main plugin, and insert them in your modules with this plugin.

Note: this branch is for ILIAS 6 and 7. If you have ILIAS 8,
see [branch release_8](https://github.com/Neuronys/ilias-pgcp_nolej/tree/release_8).

## Requirements

* ILIAS 6.x - 7.x
* [Nolej AI for ILIAS LMS plugin](https://github.com/Neuronys/ilias-robj_nolej) installed and updated.

## Installation

### Download the plugin

From the ILIAS directory, run:

```sh
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent
cd Customizing/global/plugins/Services/COPage/PageComponent
git clone -b release_7 https://github.com/Neuronys/ilias-robj_nolej NolejPageComponent
```

### Install the plugin

1. Go into `Administration` -> `Extending ILIAS` -> `Plugins`
2. Look for the name of this plugin
3. Click on `Actions` -> `Install`
