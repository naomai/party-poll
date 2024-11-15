<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuestUpgradeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class GuestAccountController extends Controller {
    public function store(GuestUpgradeRequest $request): RedirectResponse {
        $guestInfo = $request->validated();

        $account = Auth::user();
        if($account !== null) {
            return redirect(route("polls.index"), 303);
        }

        if(!isset($guestInfo['name'])) {
            $guestInfo['name'] = static::generateRandomName();
        }

        if(isset($guestInfo['password'])) {
            $guestInfo['password'] = Hash::make($request->get('password'));
        }

        $guest = User::create($guestInfo);

        Auth::login($guest, remember: true);
        session()->regenerate();       
        
        if(session()->has('invite_poll')) {
            $pollId = session()->get('invite_poll');
            $invitationToken = session()->get('invite_token');

            return redirect(route('invitation.consume', [
                    'poll'=>$pollId,
                    'token'=>$invitationToken,
                ]
            ), 303);
        }

        return redirect(route("polls.index"), 303);
    }

    private static function generateRandomName(): string {
        $animals = [
            "aardvark",
            "aphid",
            "baboon",
            "badger",
            "beaver",
            "beetle",
            "bilby",
            "bison",
            "blenny",
            "bluejay",
            "boa",
            "bongo",
            "bullfrog",
            "bunny",
            "camel",
            "centaur",
            "cheetah",
            "chicken",
            "chipmunk",
            "chiton",
            "cobra",
            "cougar",
            "dingo",
            "dino",
            "dolphin",
            "donkey",
            "dragon",
            "dugong",
            "eagle",
            "emu",
            "falcon",
            "ferret",
            "flytrap",
            "friesian",
            "gator",
            "goby",
            "groundhog",
            "guppy",
            "hedgehog",
            "hippo",
            "hornet",
            "hydra",
            "jackal",
            "jaguar",
            "kitten",
            "kitty",
            "kraken",
            "lemming",
            "lemur",
            "leopard",
            "lion",
            "lizard",
            "llama",
            "lobster",
            "manta",
            "mantis",
            "marmot",
            "minnow",
            "mollusk",
            "monkey",
            "muskox",
            "mussel",
            "mustang",
            "osprey",
            "ostrich",
            "otter",
            "oyster",
            "paca",
            "panda",
            "panther",
            "parrot",
            "peacock",
            "penguin",
            "pigeon",
            "pony",
            "puggles",
            "puma",
            "puppy",
            "python",
            "rabbit",
            "raccoon",
            "raptor",
            "raven",
            "rhino",
            "scallop",
            "sculpin",
            "seagull",
            "seahorse",
            "serpent",
            "skua",
            "spider",
            "squirrel",
            "starfish",
            "stingray",
            "swallow",
            "swordfish",
            "takin",
            "tapir",
            "tiger",
            "titmouse",
            "tortoise",
            "toucan",
            "tuna",
            "turkey",
            "turtle",
            "urchin",
            "viper",
            "vulture",
            "walrus",
            "weta",
            "wombat",
            "woodchuck",
            "zebra",
        ];

        $colors = [
            "hazel",
            "ruby",
            "amber",
            "sienna",
            "tanner",
            "olive",
            "raven",
            "kelly",
            "scarlet",
            "sterling",
            "crystal",
            "coral",
            "cherry",
            "goldie",
            "rusty",
            "saffron",
            "silver",
            "auburn",
            "azure",
            "sable",
            "lilac",
            "tawny",
            "garnet",
            "aqua",
            "fuchsia",
        ];

        $randomAnimal = $animals[array_rand($animals)];
        $randomColor = $colors[array_rand($colors)];

        return "{$randomColor} {$randomAnimal}";
    }

    // based on: https://www.cardboardmachete.com/blog/anonymous-users
}
