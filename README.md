# DGI Standard Derivative Examiner

## Introduction

Provides tools for identifying missing derivatives given DGI's standard content model.

## Requirements

This module requires the following modules/libraries:

* [Islandora](https://www.github.com/islandora/islandora)

## Installation

Install as usual, see
[this](https://www.drupal.org/docs/extending-drupal/installing-modules) for
further information.

## Usage

The `dgi-standard-derivative-examiner:derive` command accepts a CSV-like structure with the first column representing the node IDs to process. Patterns of execution might look like:

```bash
drush sql:query "select nid from node where type = 'islandora_object';" > nodes.csv
drush dgi-standard-derivative-examiner:derive --user=1 < nodes.csv
```

Or, without spooling to a separate file, using [GNU Parallel] with two processes:

```bash
# --pipe's interactions with --max-args is less-than straight-forward, seemingly
# processing up to --block's value (which defaults to 1M) per process
drush sql:query "select nid from node where type = 'islandora_object';" | parallel --pipe --max-args 100 --block 400 -j2 drush dgi-standard-derivative-examiner:derive --user=1
```

## Troubleshooting/Issues

Having problems or solved a problem? Contact
[discoverygarden](http://support.discoverygarden.ca).

## Maintainers/Sponsors

Current maintainers:

* [discoverygarden](http://www.discoverygarden.ca)

## Development

If you would like to contribute to this module, please check out the helpful
[Documentation](https://github.com/Islandora/islandora/wiki#wiki-documentation-for-developers),
and contact [discoverygarden](http://support.discoverygarden.ca).

## License

[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)
