<?php

use Illuminate\Database\Seeder;

use App\PageContent;

class PageContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
			1 => [
                'page_master_id' => 2,
                'slug' => 'banner',
                'title' => 'Search Doctor, Make an Appointment',
                'sub_title' => 'Discover the best doctors, clinic & hospital the city nearest to you.',
                'content' => '',
            ],
            2 => [
                'page_master_id' => 2,
                'slug' => 'looking_for',
                'title' => 'What are you looking for?',
                'sub_title' => '',
                'content' => '',
            ],
            3 => [
                'page_master_id' => 2,
                'slug' => 'specialities',
                'title' => 'Specialities',
                'sub_title' => '',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqa.',
            ],
            4 => [
                'page_master_id' => 2,
                'slug' => 'book_doctor',
                'title' => 'Book Our Doctor',
                'sub_title' => "",
                'content' => "Lorem Ipsum is simply dummy text

                It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum.
                
                web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes",
            ],
            5 => [
                'page_master_id' => 2,
                'slug' => 'features',
                'title' => 'Availabe Features in Our Clinic',
                'sub_title' => '',
                'content' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.',
            ],
            6 => [
                'page_master_id' => 2,
                'slug' => 'blogs',
                'title' => 'Blogs and News',
                'sub_title' => '',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            ],
            7 => [
                'page_master_id' => 2,
                'slug' => 'login',
                'title' => 'Login',
                'sub_title' => '',
                'content' => '',
            ],
            
        ];
            foreach ($datas as $id => $data) {
                $row = PageContent::firstOrNew([
                    'id' => $id,
                ]);
                $row->fill($data);
                $row->save();
            }
    }
}
