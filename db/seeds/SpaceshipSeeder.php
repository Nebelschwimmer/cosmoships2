<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class SpaceshipSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'id'=> 1,
                'user_id' => 1,
                'name' => 'Millennium Falcon',
                'description' => 'The Millennium Falcon is a large starfighter used by the Rebel Alliance and the Resistance during the Clone Wars.',
                'category_id' => 1,
                'image' => '/uploads/spaceships/millennium_falcon.jpg',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'=> 2,
                'user_id' => 1,
                'name' => 'Star Destroyer',
                'description' => 'The Star Destroyer is a large space cruiser used by the Galactic Empire during the Clone Wars.',
                'category_id' => 1,
                'image' => '/uploads/spaceships/star_destroyer.webp',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'=> 3,
                'user_id' => 1,
                'name' => 'Death Star',
                'description' => 'The Death Star is a large space station used by the Galactic Empire during the Clone Wars.',
                'category_id' => 1,
                'image' => '/uploads/spaceships/death_star.jpg',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'=> 4,
                'user_id' => 1,
                'name' => 'Hyperion Battlecruiser',
                'description' => 'Hyperion is a Behemoth-class battlecruiser, currently commanded by Admiral Matt Horner. It has a long and checkered history. ',
                'category_id' => 1,
                'image' => '/uploads/spaceships/hyperion.jpg',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'=> 5,
                'user_id' => 1,
                'name' => 'The Normandy',
                'description' => 'The SSV Normandy is a stealth frigate co-designed by the Systems Alliance and the Turian Hierarchy. It is named for the Invasion of Normandy, and is the eponymous vessel of its class. ',
                'category_id' => 1,
                'image' => '/uploads/spaceships/normandy.jpg',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->query('TRUNCATE space_ship CASCADE;')->execute();
        $this->query('TRUNCATE likes_to_spaceships CASCADE;')->execute();
        $this->query('TRUNCATE likes_to_user CASCADE;')->execute();
        $this->query('TRUNCATE publication CASCADE;')->execute();
        $this->query('TRUNCATE publications_to_user CASCADE;')->execute();
        
        
        $this->table('space_ship')->insert($data)->saveData();
        
        foreach ($data as $ship) {
            $publication = [
                'id' => $ship['id'],
                'type' => 'space_ship',
                'name' => $ship['name'],
                'created_at' => date('Y-m-d H:i:s'),
                'likes_count' => 0
            ];

            $this->table('publication')->insert($publication)->saveData();
            $this->table('publications_to_user')->insert([
                'user_id' => $ship['user_id'],
                'publication_id' => $publication['id']
            ])->saveData();
        }
    }
}
