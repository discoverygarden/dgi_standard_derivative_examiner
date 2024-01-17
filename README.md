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

The `dgi-standard-derivative-examiner:derive` command accepts a CSV-like structure with the first column representing the node IDs to process.

```
$ ddev drush dgi-standard-derivative-examiner:derive --help
Given node IDs on stdin, report on or derive derivatives.

Outputs to STDOUT.

Options:
 --dry-run[=DRY-RUN]             Flag to avoid making changes.
 --model-uri=MODEL-URI           One (or more, comma-separated) model URIs to which to filter.
 --source-use-uri=SOURCE-USE-URI One (or more, comma-separated) media use URIs to which to filter.
 --dest-use-uri=DEST-USE-URI     One (or more, comma-separated) media use URIs to which to filter.
 --fields[=FIELDS]               Comma-separated listing of fields. [default:
                                 nid,model_uri,model_plugin,target_plugin,target_uri,expected,exists,message]
--u, --user=USER                 The Drupal user as whom to run the command.

[...]

Aliases: dsde:d
```

Patterns of execution might look like:

```bash
drush sql:query "select nid from node where type = 'islandora_object';" > nodes.csv
drush dgi-standard-derivative-examiner:derive --user=1 < nodes.csv
```

Or, without spooling to a separate file, using [GNU Parallel] with two processes
each processing 100 items at a time:

```bash
drush sql:query "select nid from node where type = 'islandora_object';" | parallel --pipe --max-args 100 -j2 drush dgi-standard-derivative-examiner:derive --user=1
```

There's a balance here somewhere between:
- The number of workers
  - Likely related in some respect to the number of cores available for Drupal and the SQL server in general
- The number of items per worker
  - bootstrapping each worker takes time, but Drupal has a habit of hanging on to loaded entities longer than expected, and we do not want the process to fail due to memory exhaustion. Might be safe-ish up to 1000, in most environments?

The `dgi-standard-derivative-examiner:derive` command defaults to outputting CSV containing:
- `nid`: the node ID
- `model_uri`: the model URI processed for the node
- `model_plugin`: the associated model plugin ID
- `target_plugin`: the target plugin ID processed on the row
- `target_uri`: the target/destination media use URI for the row
- `expected`: a boolean, for if the target is expected
  - generally, if the source "original file" exists, the target is expected to exist
- `exists`: a boolean, for if the target _does_ exist
- `message`: some descriptive text for the status of the row

The four columns related to the targets are nullable, if a model is found to be in use by a node that does not have one of our plugins describing it.

## Troubleshooting/Issues

Having problems or solved a problem? Contact
[discoverygarden](http://support.discoverygarden.ca).

[GNU parallel]'s `--csv` mode appears to break the use of `--max-args`,
necessitating the use of `--block` to limit how much work each spawned child
process receives; otherwise, each apparently receives the default `--block` size
of `1M`.

## Maintainers/Sponsors

Current maintainers:

* [discoverygarden](http://www.discoverygarden.ca)

## Development

If you would like to contribute to this module, please check out the helpful
[Documentation](https://github.com/Islandora/islandora/wiki#wiki-documentation-for-developers),
and contact [discoverygarden](http://support.discoverygarden.ca).

## License

[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)

[GNU parallel]: https://www.gnu.org/software/parallel/
