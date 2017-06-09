<?php
class Readability {

	private $writing_sample;
	private $words = [];

	private $syllable_count = 0;
	private $word_count = 0;
	private $sentence_count = 0;
	public $asl = 0;
	public $asw = 0;

	// exceptions to the one syllabul to one vowel rule
	private $substract_syllable_patterns = [
		"cia(l|$)",
		"tia",
		"cius",
		"cious",
		"[^aeiou]giu",
		"[aeiouy][^aeiouy]ion",
		"iou",
		"sia$",
		"eous$",
		"[oa]gue$",
		".[^aeiuoycgltdb]{2,}ed$",
		".ely$",
		"^jua",
		"uai",
		"eau",
		"[aeiouy](b|c|ch|d|dg|f|g|gh|gn|k|l|ll|lv|m|mm|n|nc|ng|nn|p|r|rc|rn|rs|rv|s|sc|sk|sl|squ|ss|st|t|th|v|y|z)e$",
		"[aeiouy](b|c|ch|dg|f|g|gh|gn|k|l|lch|ll|lv|m|mm|n|nc|ng|nch|nn|p|r|rc|rn|rs|rv|s|sc|sk|sl|squ|ss|th|v|y|z)ed$",
		"[aeiouy](b|ch|d|f|gh|gn|k|l|lch|ll|lv|m|mm|n|nch|nn|p|r|rn|rs|rv|s|sc|sk|sl|squ|ss|st|t|th|v|y)es$",
		"^busi$"
	];

	private $add_syllable_patterns = [
		"([^s]|^)ia",
		"iu",
		"io",
		"eo($|[b-df-hj-np-tv-z])",
		"ii",
		"[ou]a$",
		"[aeiouym]bl$",
		"[aeiou]{3}",
		"[aeiou]y[aeiou]",
		"^mc",
		"ism$",
		"asm$",
		"thm$",
		"([^aeiouy])\1l$",
		"[^l]lien",
		"^coa[dglx].",
		"[^gq]ua[^auieo]",
		"dnt$",
		"uity$",
		"[^aeiouy]ie(r|st|t)$",
		"eings?$",
		"[aeiouy]sh?e[rsd]$",
		"iell",
		"dea$",
		"real",
		"[^aeiou]y[ae]",
		"gean$",
		"riet",
		"dien",
		"uen"
	];

	private $prefix_and_suffix_patterns = [
		"^un",
		"^fore",
		"^ware",
		"^none?",
		"^out",
		"^post",
		"^sub",
		"^pre",
		"^pro",
		"^dis",
		"^side",
		"ly$",
		"less$",
		"some$",
		"ful$",
		"ers?$",
		"ness$",
		"cians?$",
		"ments?$",
		"ettes?$",
		"villes?$",
		"ships?$",
		"sides?$",
		"ports?$",
		"shires?$",
		"tion(ed)?$"
	];

	private $problem_words = [
		'abalone' => 4,
		'abare' => 3,
		'abed' => 2,
		'abruzzese' => 4,
		'abbruzzese' => 4,
		'aborigine' => 5,
		'acreage' => 3,
		'adame' => 3,
		'adieu' => 2,
		'adobe' => 3,
		'anemone' => 4,
		'apache' => 3,
		'aphrodite' => 4,
		'apostrophe' => 4,
		'ariadne' => 4,
		'cafe' => 2,
		'calliope' => 4,
		'catastrophe' => 4,
		'chile' => 2,
		'chloe' => 2,
		'circe' => 2,
		'coyote' => 3,
		'epitome' => 4,
		'forever' => 3,
		'gethsemane' => 4,
		'guacamole' => 4,
		'hyperbole' => 4,
		'jesse' => 2,
		'jukebox' => 2,
		'karate' => 3,
		'machete' => 3,
		'maybe' => 2,
		'people' => 2,
		'recipe' => 3,
		'sesame' => 3,
		'shoreline' => 2,
		'simile' => 3,
		'syncope' => 3,
		'tamale' => 3,
		'yosemite' => 4,
		'daphne' => 2,
		'eurydice' => 4,
		'euterpe' => 3,
		'hermione' => 4,
		'penelope' => 4,
		'persephone' => 4,
		'phoebe' => 2,
		'zoe' => 2
	];

	public function __construct ($writing_sample) {
		$this->writing_sample = strtolower($writing_sample);
		$this->count_sentences();
		$this->count_words();
		$this->count_syllables();
		$this->get_asl();
		$this->get_asw();
	}

	public function count_sentences () {
		$sentences[] = preg_split('/[.!:;]/', $this->writing_sample, null, PREG_SPLIT_NO_EMPTY);
		foreach ($sentences as $sentence) {
			foreach ($sentence as $idx) {
				$this->sentence_count ++;
			}
		}
		return $this->sentence_count;
	}

	public function count_words () {
		$raw_words[] = preg_split('/ /', $this->writing_sample, null, PREG_SPLIT_NO_EMPTY);

		foreach ($raw_words as $word) {
			foreach ($word as $idx) {
				$this->word_count ++;
				//while we are in here lets create an array of words for syllable calculations
				$this->words[] = $idx;
			}
		}
		return $this->word_count;
	}

	public function count_syllables () {
		if (empty($this->words)) $this->word_count();

		foreach ($this->words as $idx => $word) {
			// These patterns would be normally counted as two syllables but SHOULD be one syllable. May be incomplete; do not modify.
			foreach ($this->substract_syllable_patterns as $regex) {
				if (preg_match_all('/'.$regex.'/', $word, $syllables[])) {
					unset($this->words[$idx]);
					$this->syllable_count ++;
				}
			}
			unset($regex);
			// These patterns might be counted as one syllable according to $subtract_syllable_patterns and the base rules but SHOULD be two syllables. May be incomplete; do not modify.
			foreach ($this->add_syllable_patterns as $regex) {
				if (preg_match_all('/'.$regex.'/', $word, $syllables[])) {
					unset($this->words[$idx]);
					$this->syllable_count += 2;
				}
			}
			unset($regex);

			// Single syllable prefixes and suffixes. May be incomplete; do not modify.
			foreach ($this->prefix_and_suffix_patterns as $regex) {
				if (preg_match_all('/'.$regex.'/', $word, $syllables[])) {
					$this->syllable_count ++;
					unset($this->words[$idx]);
				}
			}
			unset($regex);

			// Specific common exceptions that don't follow the rule set below are handled individually. The correct syllable count is the value. May be incomplete; do not modify.
			foreach ($this->problem_words as $problem_word) {
				if ($word == $problem_word) {
					unset($this->words[$idx]);
					$this->syllable_count += $idx;
				}
			}
			unset($problem_word);
		}
		unset($word);

		//loop through the new words array
		foreach ($this->words as $word) {
			$this->syllable_count += preg_match_all('/[aeiouy]/', $word, $syllables[]);
		}
		unset($word);
		return $this->syllable_count;
	}

	public function get_asl () {
		return $this->asl = $this->word_count/$this->sentence_count;
	}

	public function get_asw () {
		return $this->asw = $this->syllable_count/$this->word_count;
	}

	public function ease_score () {
		# Calculate score
		# Return of 0.0 to 100.0
		return number_format(206.835 - (1.015 * $this->asl) - (84.6 * $this->asw), 2);
	}
}

$sample = 'Heavy metals are generally defined as metals with relatively high densities, atomic weights, or atomic numbers. The criteria used, and whether metalloids are included, vary depending on the author and context. In metallurgy, for example, a heavy metal may be defined on the basis of density, whereas in physics the distinguishing criterion might be atomic number, while a chemist would likely be more concerned with chemical behavior. More specific definitions have been published, but none of these have been widely accepted. The definitions surveyed in this article encompass up to 96 out of the 118 chemical elements; only mercury, lead and bismuth meet all of them.';

$readability = new Readability($sample);

/*
 * Rating Scale
 * 90-100 : Very Easy
 * 80-89 : Easy
 * 70-79 : Fairly Easy
 * 60-69 : Standard
 * 50-59 : Fairly Difficult
 * 30-49 : Difficult
 * 0-29 : Very Confusing
 */

echo $readability->ease_score();
echo '<br>';
echo "\n";
?>
