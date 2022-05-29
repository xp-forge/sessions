<?php namespace web\session\cookie;

/**
 * Compression using LZW
 *
 * @see   http://www.rosettacode.org/wiki/LZW_compression#Simpler_Version
 * @test  web.session.unittest.CompressionTest
 */
class Compression {

  /**
   * Returns whether it's worthwhile compressing a given string
   * or a given number of bytes.
   *
   * @param  int|string $source
   * @return bool
   */
  public function worthwhile($source) {
    return (is_int($source) ? $source : strlen($source)) > 72;
  }

  /**
   * Yields input as codes
   *
   * @param  string $input
   * @return iterable
   */
  public function codes($input) {
    $dictionary= [];
    for ($i= 0; $i < 256; $i+= 1) {
      $dictionary[chr($i)]= $i;
    }

    $word= '';
    for ($i= 0, $s= strlen($input); $i < $s; $i+= 1) {
      $l= $word.$input[$i];
      if (isset($dictionary[$l])) {
        $word= $l;
      } else {
        yield $dictionary[$word];
        $dictionary[$l]= sizeof($dictionary);
        $word= $input[$i];
      }
    }
    if ('' !== $word) yield $dictionary[$word];
  }

  /**
   * Compress the given source
   * 
   * @param  string $source
   * @return string
   */
  public function compress($input) {
    $r= '';
    $bit= 0;
    $rem= 0;
    $bits= 8;
    $dict= 256;

    // Stream codes from input, encoding them to binary
    foreach ($this->codes($input) as $code) {
      $rem= ($rem << $bits) + $code;
      $bit+= $bits;
      $dict++;
      if ($dict >> $bits) $bits++;

      while ($bit > 7) {
        $bit-= 8;
        $r.= chr($rem >> $bit);
        $rem&= (1 << $bit) - 1;
      }
    }
    return $r.($bit ? chr($rem << (8 - $bit)) : '');
  }

  public function decompress($input) {
    if ('' === $input) return '';

    // Initialize with first byte...
    $dictionary= range("\x00", "\xff");
    $r= $word= $input[0];
    $bit= 0;
    $rem= 0;
    $bits= 9;
    $dict= 257;

    // ...then continue with rest
    for ($i= 1, $s= strlen($input); $i < $s; $i++) {
      $rem= ($rem << 8) + ord($input[$i]);
      $bit+= 8;
      if ($bit < $bits) continue;

      $bit-= $bits;      
      $entry= $dictionary[$rem >> $bit] ?? $word.$word[0];
      $r.= $entry;
      $dictionary[]= $word.$entry[0];
      $word= $entry;

      $rem&= (1 << $bit) - 1;
      $dict++;
      if ($dict >> $bits) $bits++;
    }
    return $r;
  }
}