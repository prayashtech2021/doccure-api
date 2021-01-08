<?php

use App\ { User, Doctor, Patient, MentorDetail, EducationDetail, ExperienceDetail, AwardDetail, ClinicDetail, RegDetail, MembershipDetail };
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Admin seed
        factory(User::class, 1)->create([
        	'name' => 'superadmin',
        	'slug' => 'superadmin',
        	'email' => 'superadmin@doccure.com',
        	'role' => 'admin'
        ]);

        // Doctor seed
    	factory(User::class, 1)->create([ 'email' => 'doctor@doccure.com','role' => 'doctor' ])->each(function ($user) {
            $this->doctorSeed($user);
	    });

        // Patient seed
    	factory(User::class, 1)->create([ 'email' => 'patient@doccure.com','role' => 'patient' ])->each(function ($user) {
            $this->patientSeed($user);
	    });
    }

    // Doctor sub tables seed
    public function doctorSeed($user) {
        $user->doctor()->save(factory(Doctor::class)->make());
        $user->doctorMentor()->save(factory(MentorDetail::class)->make());
        $user->doctorEducation()->save(factory(EducationDetail::class)->make());
        $user->doctorExperience()->save(factory(ExperienceDetail::class)->make());
        $user->doctorAward()->save(factory(AwardDetail::class)->make());
        $user->clinic()->save(factory(ClinicDetail::class)->make());
        $user->registrations()->save(factory(RegDetail::class)->make());
        $user->memberships()->save(factory(MembershipDetail::class)->make());
        $user->specialities()->attach(1);
        return true;
    }

    // Patient sub tables seed
    public function patientSeed($user) {
        $user->patient()->save(factory(Patient::class)->make());
    }
}
