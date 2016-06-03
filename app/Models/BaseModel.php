<?php
namespace StudentCentralCourseBrowser\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * Created by
 * User: IU Communications
 * Date: 6/3/16
 */
class BaseModel extends Model
{
    protected $connection = 'student_central_db';

}