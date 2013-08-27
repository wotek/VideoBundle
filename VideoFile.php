<?php

namespace Wtk\VideoBundle;

use Symfony\Component\HttpFoundation\File\File;

class VideoFile extends File
{
  /**
   * @var string
   */
  protected $title;

  /**
   * @var string
   */
  protected $description;

  /**
   * @param string $title
   */
  public function setTitle($title)
  {
    $this->title = $title;
  }

  /**
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * @param string $description
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }

  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * @return boolean
   */
  public function hasTitle()
  {
    return null !== $this->title;
  }

  /**
   * @return boolean
   */
  public function hasDescription()
  {
    return null !== $this->description;
  }
}
