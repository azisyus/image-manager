<?php

namespace Azizyus\ImageManager\DB\Test;


use Azizyus\ImageManager\DB\Relation;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{

    use Relation;

    protected $table = 'test_models';

    protected $fillable = ['name'];



}
