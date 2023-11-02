<?php

namespace App\Http\Livewire;

use App\Models\Client;
use App\Models\SecretCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Enregistrement extends Component
{
    use WithFileUploads;
    public $audj, $now, $nom, $numero, $filename, $type_doc, $code_secret, $code_secret_confirmation, $randomSixDigitNumber, $url, $imgUrl, $responseMessage;
    public $currentStep = 1;
    public $totalStep = 4;
    public $errorMsg = "Ce champ ne doit etre vide !";
    public $errorColor = "ring-2 ring-red-700 ring-inset";

    public function mount()
    {
        $this->audj = Carbon::today()->format('Y-m-d');
        $this->now = Carbon::now();
        $this->url = "images/addphoto.png";
        $this->imgUrl = "";
    }


    /*  public function rules() 
    {
        return [ 
            'numero' => 'required|unique:clients|min:8|max:8', 
            'nom' => 'required', 
            'file' => 'image|max:1024',
            'code_secret' => 'required|min:6|max:6', 
        ];
    } */

    public function messages()
    {
        return [
            'numero.required' => 'Vous devez entrer un numéro de telephone.',
            'numero.unique' => 'Vous avez déja fourni les infos pour ce numéro de telephone.',
            'numero.max' => 'Vous devez entrer un numéro de 8 chiffres',
            'numero.min' => 'Vous devez entrer un numéro de 8 chiffres',
            'nom.required' => 'Vous devez entrer votre nom.',
            'code_secret_confirmation.required' => 'Vous devez entrer le code qui vous a été envoyé par sms.',
            'code_secret_confirmation.min' => 'Le code secret est de 6 chiffres',
            'code_secret_confirmation.max' => 'Le code secret est de 6 chiffres',
            'code_secret.confirmed' => 'Le code entré ne correspond pas.',
            'filename.required' => "Vous devez entrer une image de votre pièce d'identité.",
        ];
    }

    public function step_increment()
    {
        /* $this->resetErrorBag(); 
        $this->validateData();*/
        $this->currentStep++;
    }

    public function step_decrement()
    {
        /* $this->resetErrorBag(); 
        if ($this->currentStep > 1) {
            $this->currentStep--;
        } */
        $this->currentStep--;
    }

    public function validateData()
    {
        if ($this->currentStep == 1) {
            $this->step1a();
        } elseif ($this->currentStep == 2) {
            $this->validate([
                'code_secret' => 'confirmed',
                'code_secret_confirmation' => 'required|min:6|max:6',
            ]);
        } elseif ($this->currentStep == 3) {
            $this->validate([
                'nom' => 'required',
            ]);
        }
    }

    public function step1a()
    {
        $this->validate([
            'numero' => 'required|unique:clients|min:8|max:8',
        ]);

        $check_client = Client::where('numero', $this->numero)->exists();
        if ($check_client) {
        } else {
            $this->sendSecretCode();
        }
    }

    public function sendSecretCode()
    {
        $checkCodeExist = SecretCode::where('numero', $this->numero)->first();
        if ($checkCodeExist) {
            $randomSixDigitNumber = rand(100000, 999999);
            $checkCodeExist->update([
                'code' => $randomSixDigitNumber,
                'date_envoie' => Carbon::now(),
            ]);

            $this->code_secret = $randomSixDigitNumber;
            $this->currentStep = 2;
        } else {
            $randomSixDigitNumber = rand(100000, 999999);
            $newSecretCode = new Secretcode();
            $newSecretCode->numero = $this->numero;
            $newSecretCode->code = $randomSixDigitNumber;
            $newSecretCode->date_envoie = Carbon::now();
            $query = $newSecretCode->save();


            $this->code_secret = $randomSixDigitNumber;
            $this->currentStep = 2;
        }

        $response = Http::asForm()->post('http://192.168.100.183:8000/api/insert', [
            'dir_num' => "253".$this->numero,
            'sms_text' => "Votre code secret est: ".$this->code_secret,
        ]);

        if ($response->successful()) {
            // Request was successful
            $this->responseMessage = $response->json()['message'];
            $this->dispatchBrowserEvent(
                'alert',
                ['type' => 'success',  'message' => "Le sms vous a été envoyé avec succès!"]
            );
        } else {
            // Request failed
            $this->responseMessage = 'Error: ' . $response->status();
            $this->dispatchBrowserEvent(
                'alert',
                ['type' => 'error',  'message' => "ll y a eu un problème lors de l'envois du sms. Veuillerc cliquer sur le bouton pour Réenvoyer !"]
            );
        }
    }


    public function step2a()
    {

        $this->validate([
            'code_secret_confirmation' => 'required|min:6|max:6',
        ]);

        //$secretCode = Secretcode::select('code')->where('numero', $this->numero)->first();

        if ($this->code_secret == $this->code_secret_confirmation) {
            $this->currentStep = 3;
        } else {
            $this->addError('code_secret_confirmation', 'Le code entré ne correspond pas au code qui vous a été envoyé.');
        }
    }
    public function updated($propertyName)
    {
        /* $this->validateOnly($propertyName); */

        if ($this->filename) {
            $this->url =  $this->filename->temporaryUrl();
        }
    }

    public function save()
    {
        $this->validate([
            'filename' => 'required|image|max:1024',
            'nom' => 'required',
            'type_doc' => 'required',
        ]);

        $client = new Client();
        $client->numero = $this->numero;
        $client->nom = Str::title($this->nom);
        $client->type_doc = $this->type_doc;
        $image_name = time() . '.' . $this->filename->getClientOriginalName();
        $client->filename =  $image_name;
        $client->public_path = "public/images/" . $image_name;
        $client->storage_path = "storage/images/" . $image_name;
        $query = $client->save();

        if ($query) {
            $this->filename->storeAs('public/images', $image_name);
            $this->currentStep = 4;
        } else {
            $this->currentStep = 5;
        }
    }

    public function render()
    {
        return view('livewire.enregistrement');
    }
}
