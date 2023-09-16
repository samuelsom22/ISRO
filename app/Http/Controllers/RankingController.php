<?php

namespace App\Http\Controllers;

use App\Models\SRO\Shard\Char;
use App\Models\SRO\Shard\CharUniqueKill;
use App\Models\SRO\Shard\Guild;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index()
    {
        $rankings = (new Char)->getPlayerRanking();
        return view('ranking.index', [
            'rankings' => $rankings,
        ]);
    }

    public function player()
    {
        $players = (new Char)->getPlayerRanking();
        return view('ranking.ranking.player', [
            'players' => $players,
        ]);
    }

    public function guild()
    {
        $guilds = (new Char)->getGuildRanking();
        return view('ranking.ranking.guild', [
            'guilds' => $guilds,
        ]);
    }

    public function unique()
    {
        $uniques = (new Char)->getUniqueRanking();
        return view('ranking.ranking.unique', [
            'uniques' => $uniques,
        ]);
    }

    public function character_view($name)
    {
        $charID = cache()->remember('char_id_' . $name, setting('cache_info_char', 600), function() use ($name) {
            return Char::select('CharID')->where('CharName16', $name)->first()->CharID ?? null;
        });

        if ($charID) {

            $characters = (new Char)->getCharInfo($charID);
            $charUniqueHistory = (new Char)->getCharUniqueHistory($charID);

            if ($characters) {
                return view('ranking.character.index', [
                    'characters' => $characters,
                    'charUniqueHistory' => $charUniqueHistory
                ]);
            }
        }
        return redirect()->back();
    }

    public function guild_view($name)
    {
        $guildID = cache()->remember('guild_id_' . $name, setting('cache_info_guild', 600), function() use ($name) {
            return Guild::select('ID')->where('Name', $name)->first()->ID ?? null;
        });

        if ($guildID) {

            $guilds = (new Guild)->getGuildInfo($guildID);
            $guildMembers = (new Guild)->getGuildInfoMembers($guildID);

            if ($guilds) {
                return view('ranking.guild.index', [
                    'guilds' => $guilds,
                    'guildMembers' => $guildMembers,
                ]);
            }
        }

        return redirect()->back();
    }
}
