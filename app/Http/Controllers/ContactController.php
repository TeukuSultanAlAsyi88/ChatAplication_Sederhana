<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::with('target')
            ->where('owner_user_id', auth()->id())
            ->orderBy('saved_name')
            ->get();

        return view('contacts.index', compact('contacts'));
    }

    public function create(Request $request)
    {
        $phone = $request->query('phone');
        return view('contacts.create', compact('phone'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'saved_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $target = User::where('phone', $data['phone'])->first();

        if (!$target) {
            return back()->with('error', 'Nomor HP belum terdaftar.')->withInput();
        }

        if ($target->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menambahkan akun sendiri.')->withInput();
        }

        $exists = Contact::where('owner_user_id', auth()->id())
            ->where('target_user_id', $target->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kontak sudah ada.')->withInput();
        }

        Contact::create([
            'owner_user_id' => auth()->id(),
            'target_user_id' => $target->id,
            'saved_name' => $data['saved_name'],
            'phone' => $data['phone'],
        ]);

        return redirect()->route('contacts.index')->with('success', 'Kontak berhasil ditambahkan.');
    }

    public function show(Contact $contact)
    {
        abort_unless($contact->owner_user_id === auth()->id(), 403);
        $contact->load('target');

        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        abort_unless($contact->owner_user_id === auth()->id(), 403);

        return view('contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        abort_unless($contact->owner_user_id === auth()->id(), 403);

        $data = $request->validate([
            'saved_name' => ['required', 'string', 'max:120'],
        ]);

        $contact->update([
            'saved_name' => $data['saved_name'],
        ]);

        return redirect()->route('contacts.index')->with('success', 'Nama kontak berhasil diperbarui.');
    }

    public function destroy(Contact $contact)
    {
        abort_unless($contact->owner_user_id === auth()->id(), 403);
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Kontak berhasil dihapus.');
    }
}
