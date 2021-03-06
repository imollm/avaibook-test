<?php

namespace App\Services;

use App\Repository\AdRepository;
use App\Rules\PictureRules, App\Rules\DescriptionRules;

class CalculateScoresService
{
    public AdRepository $adRepository;

    public function __construct(AdRepository $adRepository)
    {
        $this->adRepository = $adRepository;
    }

    public function exec(): array
    {
        $ads = $this->adRepository->getAll();

        foreach ($ads as $ad) {
            $score = $this->calculateScore($ad);
            $this->adRepository->updateScore($ad['id'], $score);
            if ($this->isIrrelevant($score))
                $this->adRepository->updateIrrelevantSince($ad['id']);
        }

        return ['message' => 'scores are calculated'];
    }

    public function calculateScore(array $ad): int
    {
        $score = 0;

        $score += count($ad['pictures']) > 0 ?
            $this->picturesScore($ad['pictures']) : PictureRules::NO_PHOTO;
        $score  += $this->descriptionScore($ad)
                + $this->wordsInDescription($ad['description'])
                + $this->completeAd($ad);

        return $this->normalizeScore($score);
    }

    public function isIrrelevant(int $score)
    {
        return $score < 40;
    }

    public function picturesScore(array $pictures): int
    {
        $score = 0;

        foreach($pictures as $picture) {
            $score += PictureRules::score($picture['quality']);
        }
        return $score;
    }

    public function descriptionScore(array $ad): int
    {
        $score = 0;
        $typology = $ad['typology'];
        $description = $ad['description'];
        $descriptionCountWords = $this->countWords($description);

        if ($descriptionCountWords <= 0)
            return 0;

        $score += DescriptionRules::HAVE_DESCRIPTION;

        if ($typology === 'FLAT') {
            if (20 <= $descriptionCountWords && $descriptionCountWords <= 49) {
                $score += DescriptionRules::FLAT_BETWEEN_20_AND_49;
            }
            else if ($descriptionCountWords >= 50) {
                $score += DescriptionRules::FLAT_EQUAL_OR_MORE_THAN_50;
            }
        }
        else if ($typology === 'CHALET') {
            if ($descriptionCountWords > 50) {
                $score += DescriptionRules::CHALET_EQUAL_OR_MORE_THAN_50;
            }
        }
        return $score;
    }

    public function countWords(string $string): int
    {
        return str_word_count($string, 0, DescriptionRules::LETTERS_WITH_ACCENT);
    }

    public function wordsInDescription(string $description): int
    {
        $regex = '/'.$this->withOutAccents(DescriptionRules::HAVE_THIS_WORDS).'/i';
        $count = preg_match_all($regex, $this->withOutAccents($description));

        return $count * DescriptionRules::FOR_EVERY_WORD;
    }

    private function withOutAccents(string $str): string
    {
        $unwanted_array = array('??'=>'S', '??'=>'s', '??'=>'Z', '??'=>'z', '??'=>'A', '??'=>'A', '??'=>'A', '??'=>'A', '??'=>'A', '??'=>'A', '??'=>'A', '??'=>'C', '??'=>'E', '??'=>'E',
            '??'=>'E', '??'=>'E', '??'=>'I', '??'=>'I', '??'=>'I', '??'=>'I', '??'=>'N', '??'=>'O', '??'=>'O', '??'=>'O', '??'=>'O', '??'=>'O', '??'=>'O', '??'=>'U',
            '??'=>'U', '??'=>'U', '??'=>'U', '??'=>'Y', '??'=>'B', '??'=>'Ss', '??'=>'a', '??'=>'a', '??'=>'a', '??'=>'a', '??'=>'a', '??'=>'a', '??'=>'a', '??'=>'c',
            '??'=>'e', '??'=>'e', '??'=>'e', '??'=>'e', '??'=>'i', '??'=>'i', '??'=>'i', '??'=>'i', '??'=>'o', '??'=>'n', '??'=>'o', '??'=>'o', '??'=>'o', '??'=>'o',
            '??'=>'o', '??'=>'o', '??'=>'u', '??'=>'u', '??'=>'u', '??'=>'y', '??'=>'b', '??'=>'y' );
        return strtr( $str, $unwanted_array );
    }

    public function completeAd(array $ad): int
    {
        $type = ['FLAT', 'GARAGE', 'CHALET'];
        $typology = $ad['typology'];

        if (
        is_int(array_search($typology, $type)) &&
        count($ad['pictures']) > 0 &&
        is_int($ad['houseSize'])
        ) {
            if (!empty($ad['description'])) {
                if ($typology === 'CHALET' && is_int($ad['gardenSize']))
                    return DescriptionRules::COMPLETE_AD;
                else if ($typology === 'FLAT')
                    return DescriptionRules::COMPLETE_AD;
            }
            if ($typology === 'GARAGE')
                return DescriptionRules::COMPLETE_AD;
        }
        return 0;
    }

    public function normalizeScore(int $score): int
    {
        if($score <= 0)
            return DescriptionRules::MIN_SCORE;
        else if($score >= 100)
            return DescriptionRules::MAX_SCORE;
        else
            return $score;
    }


}
