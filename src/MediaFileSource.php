<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\dgi_standard_derivative_examiner\SourceInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

class MediaFileSource implements SourceInterface {

  public function __construct(
    protected MediaInterface $media,
    protected FileInterface $file,
  ) {}

  public function getMedia() : MediaInterface {
    return $this->media;
  }

  public function getFile() : FileInterface {
    return $this->file;
  }

}
