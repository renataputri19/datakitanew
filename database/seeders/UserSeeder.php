<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Sample users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'renata@gmail.com',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('renata'),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'user@gmail.com',
                'is_bps' => false,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('renata'),
            ],
            [
                'name' => 'Admin BPS',
                'email' => 'admin.bps@gmail.com',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => true,
                'password' => Hash::make('renata'),
            ],




            // Users from DB_KANTOR.txt
            [
                'name' => 'Eko Aprianto, SST, M.T.I.',
                'email' => 'e_aprianto@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => true,
                'password' => Hash::make('340016186'),
            ],
            [
                'name' => 'Sri Desmiwati, S.ST',
                'email' => 'desmi@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340016419'),
            ],
            [
                'name' => 'Desmaini, S.Si',
                'email' => 'desmaini@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340016709'),
            ],
            [
                'name' => 'Adi Darmanto, S.E.',
                'email' => 'adi.darmanto@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340054823'),
            ],
            [
                'name' => 'Maria Lisbetaria Nababan, SST',
                'email' => 'lisbet@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340055833'),
            ],
            [
                'name' => 'Aditya Sangaji, SST, M.E',
                'email' => 'sangaji.aditya@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340056197'),
            ],
            [
                'name' => 'Retza Bahtiar Anugrah, S.Si.',
                'email' => 'retza.anugrah@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340059217'),
            ],
            [
                'name' => 'Renata Putri Henessa, S.Tr.Stat.',
                'email' => 'putri.henessa@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => true,
                'password' => Hash::make('340062664'),
            ],
            [
                'name' => 'Ema Aprilia Fitriani, SST',
                'email' => 'emaaprilia@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340055776'),
            ],
            [
                'name' => 'Suparyani, SE',
                'email' => 'suparyani@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340014458'),
            ],
            [
                'name' => 'Nina Martini',
                'email' => 'ninamartini@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340014247'),
            ],
            [
                'name' => 'Tunggul Hutabarat, SE',
                'email' => 'tunggul@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340012079'),
            ],
            [
                'name' => 'Catur Ariadi Wahyono',
                'email' => 'caturariadi@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340012071'),
            ],
            [
                'name' => 'Ridha Amalia Hakim, S.Si.',
                'email' => 'ridha.hakim@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340055474'),
            ],
            [
                'name' => 'Dekha Dwi Harianja, SST',
                'email' => 'dekha.harianja@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340057021'),
            ],
            [
                'name' => 'Adlina Khairunnisa, SST',
                'email' => 'adlina.khairunnisa@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340057261'),
            ],
            [
                'name' => 'Debora Sinaga, S.E.',
                'email' => 'debora.sinaga@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340051074'),
            ],
            [
                'name' => 'Metha Arfiandty, A.Md',
                'email' => 'metha.arfi@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340053611'),
            ],
            [
                'name' => 'M. Fadel Pahleva Yacoeb, SST',
                'email' => 'fadel.pahleva@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340058329'),
            ],
            [
                'name' => 'Anditia Pratiwi, S.Tr.Stat',
                'email' => 'anditiapr@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340058591'),
            ],
            [
                'name' => 'Tania Viona Sirait, S.Tr.Stat.',
                'email' => 'viona@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340059004'),
            ],
            [
                'name' => 'Evawane Fahma Kusumawardani, S.Tr.Stat',
                'email' => 'evawane.fahma@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340059510'),
            ],
            [
                'name' => 'Moon Bangun Simamora, A.Md, S.E.',
                'email' => 'moon@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340054830'),
            ],
            [
                'name' => 'Febry Utami, S.Tr.Stat.',
                'email' => 'febry.utami@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340058734'),
            ],
            [
                'name' => 'Ignatius Aprianto A S, S.Tr.Stat',
                'email' => 'ignatius.aprianto@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340059554'),
            ],
            [
                'name' => 'Ivana Yoselin Purba Siboro, S.Tr.Stat.',
                'email' => 'yoselin.purba@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340060699'),
            ],
            [
                'name' => 'Ratih Nurhabibah, S.Tr.Stat.',
                'email' => 'ratihnurhabibah@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340060871'),
            ],
            [
                'name' => 'Arief Tirtana',
                'email' => 'arieftirtana@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340016845'),
            ],
            [
                'name' => 'Moch Yailani',
                'email' => 'moch.yailani@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340019512'),
            ],
            [
                'name' => 'Hardoni',
                'email' => 'idon@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340018042'),
            ],
            [
                'name' => 'Dewi Feronika, A.Md.',
                'email' => 'dewifero-pppk@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340062810'),
            ],
            [
                'name' => 'Cuan Wanti Gultom, A.Md',
                'email' => 'cuan.gultom@bps.go.id',
                'is_bps' => true,
                'is_admin' => false,
                'is_superadmin' => false,
                'password' => Hash::make('340061227'),
            ],
            [
                'name' => 'Nimta Nababan, S.Mat.',
                'email' => 'nimta@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340054831'),
            ],
            [
                'name' => 'Sri Fahrina, A.Md.Stat',
                'email' => 'sri.fahrina@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340060430'),
            ],
            [
                'name' => 'Florentz Magdalena',
                'email' => 'fmagdalena@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340056837'),
            ],
            [
                'name' => 'Maulidya Fan Ghul Udzan Utami',
                'email' => 'maulidfan.ghul@bps.go.id',
                'is_bps' => true,
                'is_admin' => true,
                'is_superadmin' => false,
                'password' => Hash::make('340063310'),
            ],








        ];

        // Insert users into the database
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
