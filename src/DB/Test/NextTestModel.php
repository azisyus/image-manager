<?php

namespace Azizyus\ImageManager\DB\Test;


use Azizyus\ImageManager\DB\Relation;
use Illuminate\Database\Eloquent\Model;

class NextTestModel extends Model
{

    use Relation;

    protected $table = 'next_test_models';

    protected $fillable = ['name'];



}
