<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public $nama_karyawan;
    public $photo; // upload baru
    public $photo_profile; // dari database
    public $password;

    public function save()
    {
        /** @var User $user */
        $user = Auth::user();
        $validate = Validator::make([
            'photo' => $this->photo,
            'password' => $this->password
        ], [
            'photo' => 'nullable|file|image|mimes:jpg,jpeg,png,gif|max:10000',
            'password' => 'nullable|string|min:8'
        ], [
            'photo.image' => 'Photo harus berupa gambar',
            'photo.mimes' => 'Format foto tidak cocok',
            'photo.max' => 'Ukuran maksimal 10 MB',
            'password.min' => 'Password minimal 8 digit'
        ]);

        if ($validate->fails()) {
            $this->dispatch('absen-error', message: $validate->errors()->first());
            return;
        }

        $photoPath = null;

        if ($this->photo) {

            // 🔴 HAPUS FOTO LAMA
            if ($user->photo_profile && Storage::disk('public')->exists($user->photo_profile)) {
                Storage::disk('public')->delete($user->photo_profile);
            }

            // 🔵 UPLOAD BARU
            $uploadFile = $this->photo;

            $filename = Str::uuid() . '.' . $uploadFile->extension();
            $relativePath = 'profile/' . $filename;
            $fullPath = storage_path('app/public/' . $relativePath);

            if (!is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->decodeSplFileInfo($uploadFile);

            $image->scale(width: 1200);
            $image->save($fullPath, quality: 70);

            $photoPath = $relativePath;
        }

        $user->update([
            'photo_profile' => $photoPath ?? $user->photo_profile,
            'password' => $this->password ? $this->password : $user->password,
        ]);

        LivewireAlert::title('Berhasil Edit Profile')
            ->success()
            ->timer(3000)
            ->toast()
            ->position('top-end')
            ->show();

        $this->dispatch('profile-updated');
    }

    public function mount()
    {
        $user = Auth::user();

        $this->nama_karyawan = ucwords(strtolower($user->nama_karyawan));
        $this->photo_profile = $user->photo_profile;
    }
};
?>

<div>
    <div class="container-fluid">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-4">Profile</h5>
                    <div class="card">
                        <div class="card-body">
                            <form wire:submit.prevent="save">
                                <div style="display:flex;justify-content:center;margin-bottom:20px;">

                                    <label class="profile-hover-parent">

                                        <div style="position:relative;width:110px;height:110px;cursor:pointer;">

                                            @if ($photo)
                                            <img src="{{ $photo->temporaryUrl() }}" class="profile-img">
                                            @elseif ($photo_profile)
                                            <img src="{{ asset('storage/' . $photo_profile) }}" class="profile-img">
                                            @else
                                            <img src="../assets/images/profile/user-1.jpg" class="profile-img">
                                            @endif

                                            <!-- TEXT -->
                                            <div class="profile-hover-text">
                                                Ubah Profile
                                            </div>

                                            <input type="file" wire:model="photo" accept="image/*" hidden>

                                        </div>

                                    </label>
                                </div>
                                <fieldset disabled>
                                    <div class="mb-3">
                                        <label for="exampleInputNama1" class="form-label">Nama Karyawan</label>
                                        <input wire:model="nama_karyawan" type="text" class="form-control" id="exampleInputNama1" aria-describedby="NamaHelp">
                                        <div id="NamaHelp" class="form-text">Hubungi admin untuk mengubah nama</div>
                                    </div>
                                </fieldset>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>

                                    <div style="position:relative; display:flex; align-items:center;">

                                        <input
                                            type="password"
                                            class="form-control"
                                            id="password"
                                            style="padding-right:40px;">

                                        <span
                                            onclick="togglePassword()"
                                            style="position:absolute; right:12px; cursor:pointer; color:#888; display:flex; align-items:center; height:100%;">
                                            <i class="ti ti-eye" id="iconPassword"></i>
                                        </span>

                                    </div>
                                    <div id="PasswordHelp" class="form-text">Isi untuk mengganti password</div>
                                </div>
                                <!-- <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                                    <label class="form-check-label" for="exampleCheck1">Pastikan sudah benar</label>
                                </div> -->
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById("password");
            const icon = document.getElementById("iconPassword");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("ti-eye");
                icon.classList.add("ti-eye-off");
            } else {
                input.type = "password";
                icon.classList.remove("ti-eye-off");
                icon.classList.add("ti-eye");
            }
        }
    </script>
</div>