<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Pangkat;
use App\User;
use App\Detailkpr;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Http\Request;
use App\Exports\UserExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class AccountController extends Controller
{
    public function index()
    {
        $account = User::where('id', '!=', auth()->user()->id)->whereIn('role', ['0', '1'])->paginate(5);
        return view('admin.account.index', [
            'accounts' => $account
        ]);
    }

    public function admin_index_account()
    {
        $account = User::where('id', '!=', auth()->user()->id)->where('role', '0')->paginate(5);
        return view('admin.account.admin.index', [
            'accounts' => $account
        ]);
    }

    public function pengelola_index_account()
    {
        $account = User::where('id', '!=', auth()->user()->id)->where('role', '1')->paginate(5);
        return view('admin.account.kelola.index', [
            'accounts' => $account
        ]);
    }

    public function user_index_account()
    {
        // $detailkpr = Detailkpr::where('id', '!=', auth()->user()->id)->wherein('role', ['2', '3'])->paginate(5);
        $detailkpr = Detailkpr::paginate(20);
        return view('admin.account.user.index', [
            'accounts' => $detailkpr
            // 'pangkats' => Pangkat::get()
        ]);
    }

    public function verifikasi_index_account()
    {
        $account = User::where('id', '!=', auth()->user()->id)->where('status_verif', null || 0)->where('email_verified_at','!=',null)->whereIn('role', ['3'])->paginate(5);


        return view('admin.account.verifikasi.index', [
            'accounts' => $account
        ]);

    }

    public function update_role($id)
    {
        User::findOrFail($id)->update([
            'role' => '2'
        ]);

        Alert::success('Informasi Pesan', 'Role berhasil di update');
        return back();
    }
    public function krp_update_profile_user(Request $request)
    {
        Detailkpr::create([
            "nama" => $request->nama,
            "nrp" => $request->nrp,
            "pangkat" => $request->pangkat,
            "corps" => $request->corps,
            "kesatuan" => $request->kesatuan,
            "tahap" => $request->tahap,
            'status' => 0
        ]);

        Alert::success('Informasi Pesan', 'Role berhasil di update');

        return back();
    }

    public function verified($id)
    {
        User::findOrFail($id)->update([
            'role' => '2',
            'status_verif' => '1'
        ]);
        Alert::success('Informasi Pesan', 'User berhasil di Verifikasi');
        return back();
    }

    public function store()
    {
        $attr = $this->validate(request(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'nrp' => ['required', 'string', 'min:3', 'max:255', 'unique:users,nrp'],
            'password' => ['required', 'min:3'],
            'role' => ['required'],
            'avatar' => ['mimes:png,jpg,jpeg,svg', 'max:2048']
        ]);
        $attr['password'] = bcrypt(request('password'));
        $thumb = request()->file('avatar') ? request()->file('avatar')->store("images/avatar") : null;
        $attr['avatar'] = $thumb;
        User::create($attr);
        Alert::success('Informasi Pesan', $this->role_definition() . ' baru berhasil di simpan');
        return back();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.account.edit', compact('user'));
    }

    public function update($id)
    {
        $attr = $this->validate(request(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'nrp' => ['required', 'min:3', 'string', 'max:255', 'unique:users,nrp,' . $id],
            'avatar' => ['mimes:png,jpg,jpeg,svg', 'max:2048']
        ]);
        $user = User::findOrFail($id);
        if ($user->avatar == null) {
            if (request()->file('avatar')) {
                $thumbnail = request()->file('avatar')->store("images/avatar");
            } else {
                $thumbnail = null;
            }
        } else {
            if (request()->file('avatar')) {
                \Storage::delete($user->avatar);
                $thumbnail = request()->file('avatar')->store("images/avatar");
            } else {
                $thumbnail = $user->avatar;
            }
        }
        $attr['avatar'] = $thumbnail;
        $user->update($attr);
        Alert::success('Informasi Pesan', $this->role_definition() . ' ' . request('name') . ' berhasil di update');
        return redirect()->back();
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->avatar) {
            \Storage::delete($user->avatar);
        }
        $user->delete();
        Alert::success('Informasi Pesan', $this->role_definition() . ' ' . $user->name . ' berhasil di hapus');
        return back();
    }

    protected function role_definition()
    {

        if(request('role') == 0)
        {
            return 'Admin';
        } else {
            return 'Pengelola';
        }
    }

    public function search_admin()
    {
        $query = request('query');
        $account = User::where('role', '0')->where("name", "like", "%$query%")
            ->orWhere("email", "like", "%$query%")
            ->orWhere("nrp", "like", "%$query%")
            ->latest()->paginate(3);
        return view('admin.account.admin.index', [
            'accounts' => $account
        ]);
    }

    public function search_pengelola()
    {
        $query = request('query');
        $account = User::where('role', '1')->where("name", "like", "%$query%")
            ->orWhere("email", "like", "%$query%")
            ->orWhere("nrp", "like", "%$query%")
            ->latest()->paginate(3);
        return view('admin.account.kelola.index', [
            'accounts' => $account
        ]);
    }

    public function userExportExcel()
    {
        return Excel::download(new UserExport, 'Uji Coba.xlsx');
    }

    public function userExportPdf()
    {
        $user = Detailkpr::paginate(100);
        $pdf = PDF::loadview('admin.account.report_user_pdf',[
            'pinjams' => $user
        ]);
        return $pdf->stream();
    }

}
