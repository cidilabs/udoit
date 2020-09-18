<?php

namespace App\Lms;

use App\Entity\Course;
use App\Entity\User;

interface LmsInterface {
    public function getId();
    public function testApiConnection(User $user);
    public function updateCourseContent(Course $course, User $user);
    public function updateCourseData(Course $course, User $user);
}