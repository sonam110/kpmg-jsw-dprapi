<?php

namespace App\Traits;

trait AutoInc
{
  protected static function bootAutoInc() {
    static::creating(function ($model) {
      if (! $model->getKey()) {
        $model->{$model->getKeyName()} = (string) \Uuid::generate(4);
      }
      $model->id_inc = ($model->withoutGlobalScope('top_most_parent_id')->max('id_inc')) ? $model->withoutGlobalScope('top_most_parent_id')->max('id_inc') + 1 : 1;
    });
  }

  public function getIncrementing()
  {
    return false;
  }

  public function getKeyType()
  {
    return 'string';
  }
}                                                                                          