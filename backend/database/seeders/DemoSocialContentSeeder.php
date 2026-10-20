<?php

namespace Database\Seeders;

use App\Models\Pin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoSocialContentSeeder extends Seeder
{
    private const AUTHORS = [
        ['name' => 'Alya Pramesti', 'username' => 'alya_ruang', 'bio' => 'Menyimpan inspirasi kecil untuk rumah dan hari yang lebih hangat.'],
        ['name' => 'Raka Mahendra', 'username' => 'raka_jejak', 'bio' => 'Ide simpel untuk hidup aktif, santai, dan tetap seimbang.'],
        ['name' => 'Nadia Putri', 'username' => 'nadia_rasa', 'bio' => 'Jurnal resep rumahan, kopi sore, dan makanan enak untuk dibagi.'],
        ['name' => 'Bima Satya', 'username' => 'bima_pilih', 'bio' => 'Padu padan pakaian yang nyaman dengan sentuhan personal.'],
        ['name' => 'Keisha Aulia', 'username' => 'keisha_garis', 'bio' => 'Sketsa, warna, dan karya seni yang membuatku berhenti sejenak.'],
        ['name' => 'Dimas Rasyid', 'username' => 'dimas_hijau', 'bio' => 'Tanaman, sudut hijau, dan kebiasaan kecil yang terasa baik.'],
        ['name' => 'Sari Wulandari', 'username' => 'sari_pagi', 'bio' => 'Merayakan pagi pelan-pelan lewat rumah, makanan, dan warna.'],
        ['name' => 'Farhan Akbar', 'username' => 'farhan_coba', 'bio' => 'Eksperimen resep sederhana dan ide kreatif sehari-hari.'],
        ['name' => 'Livia Anindya', 'username' => 'livia_warna', 'bio' => 'Favoritku: kain menarik, ruang nyaman, dan seni penuh warna.'],
        ['name' => 'Niko Prakoso', 'username' => 'niko_sudut', 'bio' => 'Mengumpulkan inspirasi desain, gaya, dan hal-hal yang menyenangkan.'],
    ];

    private const POSTS_BY_CATEGORY = [
        'Gaya Hidup' => [
            ['title' => 'Pagi Tenang di Sudut Favorit', 'description' => 'Ritual kecil sebelum mulai beraktivitas: buka jendela, seduh minuman hangat, lalu menikmati cahaya pagi.'],
            ['title' => 'Jalan Santai Tanpa Terburu-buru', 'description' => 'Keluar sebentar dan menikmati lingkungan sekitar ternyata cukup untuk menyegarkan pikiran.'],
            ['title' => 'Bekal Simpel untuk Hari Sibuk', 'description' => 'Menyiapkan bekal dari rumah bikin waktu makan lebih tenang dan pilihan makan jadi lebih terencana.'],
            ['title' => 'Seragam Nyaman untuk Sehari-hari', 'description' => 'Aku suka menyimpan beberapa padu padan andalan supaya bersiap pagi terasa lebih mudah.'],
            ['title' => 'Momen Kecil, Ide Baru', 'description' => 'Mencatat inspirasi yang lewat membantu hari biasa terasa lebih berwarna dan penuh kemungkinan.'],
            ['title' => 'Menata Tanaman Sebelum Kerja', 'description' => 'Menyiram tanaman dan merapikan daunnya jadi jeda singkat yang menyenangkan di awal hari.'],
            ['title' => 'Jurnal Pagi dengan Secangkir Teh', 'description' => 'Beberapa baris catatan dan segelas teh hangat membuat pagi terasa lebih pelan dan fokus.'],
            ['title' => 'Camilan Sore Buatan Sendiri', 'description' => 'Membuat camilan sederhana di rumah adalah alasan yang pas untuk berbagi waktu dengan keluarga.'],
            ['title' => 'Warna-Warni yang Bikin Semangat', 'description' => 'Hari ini aku memilih warna cerah untuk mengingatkan diri agar berani mencoba hal baru.'],
            ['title' => 'Menikmati Istirahat dengan Sadar', 'description' => 'Berhenti sebentar, merapikan meja, dan menghirup udara segar ternyata membuatku kembali bersemangat.'],
        ],
        'Dekorasi' => [
            ['title' => 'Rak Kayu untuk Sudut Baca', 'description' => 'Rak kecil dan beberapa buku favorit cukup untuk mengubah sudut kosong jadi tempat istirahat.'],
            ['title' => 'Cahaya Hangat di Ruang Keluarga', 'description' => 'Lampu bernuansa hangat membuat ruangan terasa lebih akrab saat sore mulai gelap.'],
            ['title' => 'Meja Makan dengan Bunga Segar', 'description' => 'Vas sederhana berisi bunga dari pasar membuat waktu makan di rumah terasa lebih spesial.'],
            ['title' => 'Penyimpanan Terbuka yang Rapi', 'description' => 'Keranjang dan kotak dengan warna serasi membantu barang sehari-hari tetap mudah dijangkau.'],
            ['title' => 'Poster Favorit untuk Dinding Kosong', 'description' => 'Satu karya dengan palet warna yang pas bisa menjadi titik fokus tanpa memenuhi seluruh dinding.'],
            ['title' => 'Tanaman Hijau di Dekat Jendela', 'description' => 'Letakkan tanaman di tempat yang mendapat cahaya cukup agar sudut ruangan terasa hidup.'],
            ['title' => 'Sarapan di Teras Kecil', 'description' => 'Kursi ringan dan alas meja favorit membuat teras mungil nyaman dipakai setiap pagi.'],
            ['title' => 'Dapur Rapi dengan Label Simpel', 'description' => 'Stoples seragam dan label tulisan tangan memberi tampilan rapi sekaligus memudahkan mencari bahan.'],
            ['title' => 'Kain dan Warna untuk Kamar Nyaman', 'description' => 'Menukar sarung bantal dengan warna lembut adalah perubahan kecil dengan suasana yang berbeda.'],
            ['title' => 'Sudut Kerja yang Lebih Teratur', 'description' => 'Meja bersih, tempat alat tulis, dan satu benda kesayangan membuatku betah berkarya.'],
        ],
        'Resep' => [
            ['title' => 'Roti Panggang Alpukat untuk Pagi', 'description' => 'Roti renyah, alpukat matang, sedikit jeruk nipis, dan taburan lada jadi sarapan praktis.'],
            ['title' => 'Mangkuk Buah Segar dan Yogurt', 'description' => 'Potongan buah musiman dengan yogurt dan granola cocok untuk sarapan ringan atau camilan.'],
            ['title' => 'Nasi Goreng Sayur Andalan', 'description' => 'Manfaatkan nasi sisa dengan sayuran, bawang putih, dan kecap untuk menu rumahan yang cepat.'],
            ['title' => 'Pasta Krim Jamur yang Mudah', 'description' => 'Jamur tumis, susu, dan keju menghasilkan saus sederhana yang cocok untuk makan malam.'],
            ['title' => 'Es Teh Lemon untuk Siang Cerah', 'description' => 'Seduh teh, dinginkan, lalu tambahkan lemon dan es batu untuk minuman yang menyegarkan.'],
            ['title' => 'Sup Sayur Hangat dalam Satu Panci', 'description' => 'Wortel, kentang, dan sayuran yang ada di kulkas berubah jadi sup hangat yang mudah disiapkan.'],
            ['title' => 'Pisang Panggang dengan Kayu Manis', 'description' => 'Pisang matang yang dipanggang sebentar terasa manis dengan taburan kayu manis.'],
            ['title' => 'Salad Tomat untuk Teman Makan', 'description' => 'Tomat segar, daun hijau, dan saus lemon sederhana jadi pendamping yang ringan dan cerah.'],
            ['title' => 'Puding Cokelat Simpel untuk Berbagi', 'description' => 'Camilan cokelat lembut dengan bahan sehari-hari, enak disimpan dingin untuk dinikmati bersama.'],
            ['title' => 'Racikan Kopi Susu di Rumah', 'description' => 'Kopi seduh, susu dingin, dan es batu jadi minuman sore favorit tanpa perlu alat khusus.'],
        ],
        'Fashion' => [
            ['title' => 'Kemeja Putih dengan Denim Favorit', 'description' => 'Paduan klasik yang nyaman dipakai beraktivitas dan gampang dilengkapi aksesori sederhana.'],
            ['title' => 'Lapisan Ringan untuk Cuaca Sejuk', 'description' => 'Jaket tipis di atas kaus favorit memberi lapisan nyaman tanpa terasa berat.'],
            ['title' => 'Warna Netral untuk Sehari-hari', 'description' => 'Warna krem dan cokelat mudah dipadukan dan memberi kesan santai yang tetap rapi.'],
            ['title' => 'Sentuhan Warna di Pakaian Simpel', 'description' => 'Tas kecil berwarna cerah membuat pakaian bernuansa dasar terasa lebih personal.'],
            ['title' => 'Padu Padan Nyaman untuk Akhir Pekan', 'description' => 'Celana longgar dan atasan ringan adalah pilihan praktis untuk agenda santai di luar rumah.'],
            ['title' => 'Motif Favorit dengan Aksesori Polos', 'description' => 'Saat memilih pakaian bermotif, aku melengkapinya dengan aksesori sederhana agar tetap seimbang.'],
            ['title' => 'Tampilan Santai dengan Sepatu Kanvas', 'description' => 'Sepatu kanvas favorit cocok untuk jalan sore dan membuat banyak padu padan terasa kasual.'],
            ['title' => 'Gaya Monokrom yang Mudah Dipadu', 'description' => 'Memadukan hitam, putih, dan abu-abu membuatku siap lebih cepat tanpa kehabisan ide.'],
            ['title' => 'Scarf Kecil, Gaya Baru', 'description' => 'Scarf bermotif bisa dipakai sebagai aksen tas atau aksesori ringan untuk melengkapi penampilan.'],
            ['title' => 'Pakaian Favorit yang Dipakai Ulang', 'description' => 'Mencoba kombinasi baru dari pakaian yang sudah ada selalu lebih seru daripada belanja impulsif.'],
        ],
        'Seni' => [
            ['title' => 'Sketsa Bunga di Pagi Hari', 'description' => 'Beberapa garis pensil dan bentuk sederhana sudah cukup untuk menangkap suasana bunga di meja.'],
            ['title' => 'Kolase Warna dari Majalah Lama', 'description' => 'Potongan kertas dengan warna berbeda menjadi cara menyenangkan untuk bereksperimen tanpa takut salah.'],
            ['title' => 'Cat Air dengan Palet Senja', 'description' => 'Mencampur warna jingga dan biru sambil menunggu senja selalu menghasilkan kejutan baru.'],
            ['title' => 'Bentuk Abstrak di Buku Gambar', 'description' => 'Aku mulai dengan satu bentuk, lalu membiarkan warna dan garis membawa gambar ke arah lain.'],
            ['title' => 'Belajar Menggambar Daun', 'description' => 'Mengamati urat daun dan membuat garis pelan-pelan jadi latihan kecil yang menenangkan.'],
            ['title' => 'Kartu Ucapan Buatan Tangan', 'description' => 'Kertas sisa dan ilustrasi sederhana terasa istimewa ketika diubah menjadi ucapan untuk teman.'],
            ['title' => 'Jurnal Visual Perjalanan Kota', 'description' => 'Sketsa cepat di buku kecil membantuku mengingat bentuk bangunan dan suasana jalan yang dikunjungi.'],
            ['title' => 'Cetak Motif dari Benda Sehari-hari', 'description' => 'Daun dan cat bisa dipakai untuk mencoba cetakan unik di kertas tanpa alat yang rumit.'],
            ['title' => 'Gambar Garis untuk Mengisi Waktu', 'description' => 'Satu pulpen hitam dan kertas kosong cukup untuk berlatih membuat pola saat istirahat.'],
            ['title' => 'Eksperimen Tekstur dengan Kertas', 'description' => 'Melipat dan menumpuk potongan kertas memberi dimensi baru pada karya yang awalnya sederhana.'],
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $storage = Storage::disk('public');
            $demoUsers = [];

            foreach (self::AUTHORS as $authorIndex => $author) {
                $avatarPath = sprintf('pins/demo-%02d.jpg', $authorIndex + 1);

                if (! $storage->exists($avatarPath)) {
                    throw new \RuntimeException("Demo photo is missing: {$avatarPath}");
                }

                $email = $author['username'].'@demo.pinterus.test';
                $user = User::query()->firstOrNew(['email' => $email]);
                $user->fill([
                    'name' => $author['name'],
                    'username' => $author['username'],
                    'bio' => $author['bio'],
                    'avatar_url' => $storage->url($avatarPath),
                ]);

                if (! $user->exists) {
                    $user->password = Str::random(64);
                    $user->email_verified_at = now();
                }

                $user->save();
                $demoUsers[$authorIndex] = $user;
            }

            $imageNumber = 0;

            foreach (self::POSTS_BY_CATEGORY as $category => $posts) {
                foreach ($posts as $authorIndex => $post) {
                    $imageNumber++;
                    $imagePath = sprintf('pins/demo-%02d.jpg', $imageNumber);

                    if (! $storage->exists($imagePath)) {
                        throw new \RuntimeException("Demo photo is missing: {$imagePath}");
                    }

                    Pin::query()->updateOrCreate(
                        [
                            'user_id' => $demoUsers[$authorIndex]->id,
                            'title' => $post['title'],
                        ],
                        [
                            'description' => $post['description'],
                            'category' => $category,
                            'image_url' => $storage->url($imagePath),
                            'image_path' => $imagePath,
                            'link_url' => null,
                        ],
                    );
                }
            }
        });
    }
}
