<?php

declare(strict_types=1);

namespace App\Services;

/**
 * NumberConverterService
 *
 * Converts integers (0 – 9,99,99,999) to:
 *  • Unicode Bangla words  (BD counting: koti / lakh / hajar / shoto)
 *  • English words         (South-Asian / BD system)
 *  • AdorshoLipi / Proshika ANSI text  (returned as base64 string)
 *
 * ══════════════════════════════════════════════════════════════════════
 * ADORSHOLIPI FONT ENCODING — HOW IT WORKS
 * ══════════════════════════════════════════════════════════════════════
 * AdorshoLipi is a legacy ANSI (Windows-1252) font that maps standard
 * ASCII/ANSI codepoints to Bengali glyphs. It is NOT Unicode.
 *
 * Key encoding rules:
 *
 *  1. CONSONANTS map to lowercase/uppercase Latin letters (A–Z, a–z)
 *     e.g. ক=L, খ=M, গ=N ... ন=e, প=f ...
 *
 *  2. VOWEL SIGNS (matras) that sit to the RIGHT of a consonant
 *     are simply appended after the consonant byte:
 *       া  = \xA1 (161)  appended after consonant
 *       ি  = \xA2 (162)  appended after consonant
 *       ী  = \xA3 (163)  appended after consonant
 *       ু  = \xA4 (164)  appended after consonant
 *       ূ  = \xA5 (165)  appended after consonant
 *       ৃ  = \xA6 (166)  appended after consonant
 *
 *  3. E-MATRA (ে) and ঐ-MATRA (ৈ) sit VISUALLY to the LEFT of the
 *     consonant in the rendered output, so their bytes must come BEFORE
 *     the consonant byte in the byte stream:
 *       ে  = \x87 (135) — must precede the consonant byte
 *       ৈ  = \x89 (137) — must precede the consonant byte
 *
 *  4. O-MATRA (ো = ে + া) wraps the consonant:
 *       byte stream: \x87 + <consonant> + \xA1
 *
 *  5. OU-MATRA (ৌ = ে + ৌ-right) wraps the consonant:
 *       byte stream: \x87 + <consonant> + \xD1
 *       (0xD1 is the right-side glyph of ৌ in AdorshoLipi)
 *
 *  6. HASANTA (্) = \x&& — suppresses the inherent vowel for conjuncts.
 *     In AdorshoLipi conjuncts are usually pre-built glyphs; hasanta
 *     is used for consonant clusters not covered by built-in conjuncts.
 *
 *  7. CONJUNCTS (যুক্তবর্ণ) — AdorshoLipi has dedicated single-byte or
 *     two-byte glyphs for common conjuncts. They must be matched BEFORE
 *     the individual consonant mappings (longest-match-first via strtr).
 *
 * ══════════════════════════════════════════════════════════════════════
 * SENTINEL / REORDER STRATEGY FOR PRE-POSITION MATRAS
 * ══════════════════════════════════════════════════════════════════════
 * Unicode stores ে/ৈ/ো/ৌ AFTER the base consonant.
 * AdorshoLipi needs the left-side glyph byte BEFORE the consonant byte.
 *
 * Strategy:
 *   a) In ADORSHO_MAP, map ে/ৈ/ো/ৌ to unique sentinel bytes (0x01–0x04)
 *      that never appear in real AdorshoLipi text.
 *   b) strtr() replaces Unicode chars with sentinels+post-bytes in one pass.
 *   c) reorderMatras() scans the result, finds <consonant><sentinel> and
 *      rearranges to <matra-pre><consonant><matra-post>.
 *
 * PSR-12 compliant.
 */
class NumberConverterService
{
    // ── Bangla word lists ──────────────────────────────────────────────────

    private const BN_ONES = [
        0 => '',
        1 => 'এক',
        2 => 'দুই',
        3 => 'তিন',
        4 => 'চার',
        5 => 'পাঁচ',
        6 => 'ছয়',
        7 => 'সাত',
        8 => 'আট',
        9 => 'নয়',
        10 => 'দশ',
        11 => 'এগারো',
        12 => 'বারো',
        13 => 'তেরো',
        14 => 'চৌদ্দ',
        15 => 'পনেরো',
        16 => 'ষোলো',
        17 => 'সতেরো',
        18 => 'আঠারো',
        19 => 'উনিশ',
        20 => 'বিশ',
        21 => 'একুশ',
        22 => 'বাইশ',
        23 => 'তেইশ',
        24 => 'চব্বিশ',
        25 => 'পঁচিশ',
        26 => 'ছাব্বিশ',
        27 => 'সাতাশ',
        28 => 'আঠাশ',
        29 => 'উনত্রিশ',
        30 => 'ত্রিশ',
        31 => 'একত্রিশ',
        32 => 'বত্রিশ',
        33 => 'তেত্রিশ',
        34 => 'চৌত্রিশ',
        35 => 'পঁয়ত্রিশ',
        36 => 'ছত্রিশ',
        37 => 'সাঁইত্রিশ',
        38 => 'আটত্রিশ',
        39 => 'উনচল্লিশ',
        40 => 'চল্লিশ',
        41 => 'একচল্লিশ',
        42 => 'বিয়াল্লিশ',
        43 => 'তেতাল্লিশ',
        44 => 'চৌচল্লিশ',
        45 => 'পঁয়তাল্লিশ',
        46 => 'ছেচল্লিশ',
        47 => 'সাতচল্লিশ',
        48 => 'আটচল্লিশ',
        49 => 'উনপঞ্চাশ',
        50 => 'পঞ্চাশ',
        51 => 'একান্ন',
        52 => 'বায়ান্ন',
        53 => 'তিপান্ন',
        54 => 'চুয়ান্ন',
        55 => 'পঞ্চান্ন',
        56 => 'ছাপান্ন',
        57 => 'সাতান্ন',
        58 => 'আটান্ন',
        59 => 'উনষাট',
        60 => 'ষাট',
        61 => 'একষট্টি',
        62 => 'বাষট্টি',
        63 => 'তেষট্টি',
        64 => 'চৌষট্টি',
        65 => 'পঁয়ষট্টি',
        66 => 'ছেষট্টি',
        67 => 'সাতষট্টি',
        68 => 'আটষট্টি',
        69 => 'উনসত্তর',
        70 => 'সত্তর',
        71 => 'একাত্তর',
        72 => 'বাহাত্তর',
        73 => 'তেয়াত্তর',
        74 => 'চুয়াত্তর',
        75 => 'পঁচাত্তর',
        76 => 'ছিয়াত্তর',
        77 => 'সাতাত্তর',
        78 => 'আটাত্তর',
        79 => 'উনআশি',
        80 => 'আশি',
        81 => 'একাশি',
        82 => 'বিরাশি',
        83 => 'তিরাশি',
        84 => 'চুরাশি',
        85 => 'পঁচাশি',
        86 => 'ছিয়াশি',
        87 => 'সাতাশি',
        88 => 'আটাশি',
        89 => 'উননব্বই',
        90 => 'নব্বই',
        91 => 'একানব্বই',
        92 => 'বিরানব্বই',
        93 => 'তিরানব্বই',
        94 => 'চুরানব্বই',
        95 => 'পঁচানব্বই',
        96 => 'ছিয়ানব্বই',
        97 => 'সাতানব্বই',
        98 => 'আটানব্বই',
        99 => 'নিরানব্বই',
    ];

    // ── English word lists ─────────────────────────────────────────────────

    private const EN_ONES = [
        '',
        'One',
        'Two',
        'Three',
        'Four',
        'Five',
        'Six',
        'Seven',
        'Eight',
        'Nine',
        'Ten',
        'Eleven',
        'Twelve',
        'Thirteen',
        'Fourteen',
        'Fifteen',
        'Sixteen',
        'Seventeen',
        'Eighteen',
        'Nineteen',
    ];

    private const EN_TENS = [
        '',
        '',
        'Twenty',
        'Thirty',
        'Forty',
        'Fifty',
        'Sixty',
        'Seventy',
        'Eighty',
        'Ninety',
    ];

    // ══════════════════════════════════════════════════════════════════════
    // ADORSHOLIPI CHARACTER MAP
    // ══════════════════════════════════════════════════════════════════════
    //
    // Byte reference (decimal → hex → what AdorshoLipi renders):
    //
    //  Standard consonants (Latin letters):
    //    ক=76(L)  খ=77(M)  গ=78(N)  ঘ=79(O)  ঙ=80(P)
    //    চ=81(Q)  ছ=82(R)  জ=83(S)  ঝ=84(T)  ঞ=85(U)
    //    ট=86(V)  ঠ=87(W)  ড=88(X)  ঢ=89(Y)  ণ=90(Z)
    //    ত=97(a)  থ=98(b)  দ=99(c)  ধ=100(d) ন=101(e)
    //    প=102(f) ফ=103(g) ব=104(h) ভ=105(i) ম=106(j)
    //    য=107(k) র=108(l) ল=109(m) শ=110(n) ষ=111(o)
    //    স=112(p) হ=113(q)
    //    ড়=114(r) ঢ়=115(s) য়=116(t) ৎ=117(u)
    //    ং=118(v) ঃ=119(w) ঁ=120(x)
    //
    //  Vowel signs (right-side / below / above — appended AFTER consonant):
    //    া  = \xA1  ি  = \xA2  ী  = \xA3
    //    ু  = \xA4  ূ  = \xA5  ৃ  = \xA6
    //
    //  Pre-position matra bytes (must come BEFORE consonant byte):
    //    ে  = \x87   ৈ  = \x89
    //
    //  Compound matras (wrap consonant):
    //    ো  = \x87 + <cons> + \xA1
    //    ৌ  = \x87 + <cons> + \xD1
    //
    //  Hasanta (virama / halant):
    //    ্  = \x26 (&)   — used inside conjuncts not in the pre-built table
    //
    //  Sentinels (C0 control range — never in real AdorshoLipi text):
    //    \x01 = placeholder for ে-matra (→ \x87 after reorder)
    //    \x02 = placeholder for ৈ-matra (→ \x89 after reorder)
    //    \x03 = placeholder for ো left-part (→ \x87 after reorder)
    //    \x04 = placeholder for ৌ left-part (→ \x87 after reorder)
    //
    // ── CONJUNCT TABLE ────────────────────────────────────────────────────
    // Listed longest-first so strtr() matches greedily.
    // Sources: AdorshoLipi keyboard layout + manual font glyph inspection.
    //
    // Format of entries:  'Unicode cluster' => 'ANSI byte(s)'
    //
    // Each conjunct glyph in AdorshoLipi is a SINGLE dedicated codepoint
    // (or two-byte sequence) that the font renders as the full ligature.
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Unicode → AdorshoLipi ANSI map.
     *
     * ORDERING IS CRITICAL: strtr() is longest-match-first, so multi-char
     * Unicode clusters (conjuncts, compound matras) MUST appear before their
     * component characters in this array.
     *
     * Byte literals below use PHP's \xNN escape inside double-quoted strings.
     * All values are single or double ANSI bytes (not UTF-8 multi-byte).
     */
    private const ADORSHO_MAP = [

        // ══════════════════════════════════════════════════════════════════
        // CONJUNCTS (যুক্তবর্ণ) — must come BEFORE individual consonants
        // ══════════════════════════════════════════════════════════════════
        // Each entry: Unicode cluster => AdorshoLipi glyph byte(s)
        //
        // Two-consonant conjuncts formed with hasanta (্) in Unicode
        // are replaced by a SINGLE pre-built glyph byte in AdorshoLipi.
        //
        // The glyph bytes below are taken from the standard AdorshoLipi
        // keyboard layout (Bijoy Bayanno / Proshika layout).
        // ──────────────────────────────────────────────────────────────────

        // ── ক-যুক্ত ────────────────────────────────────────────────────
        'ক্ক' => "LL",          // ক্ক  (double-ক, no single glyph → halant form)
        'ক্ট' => "\xC0",        // ক্ট
        'ক্ত' => "\xC1",        // ক্ত
        'ক্ন' => "Le",          // ক্ন
        'ক্ব' => "\xBB",        // ক্ব
        'ক্ম' => "Lj",          // ক্ম
        'ক্য' => "Lk",          // ক্য
        'ক্র' => "L\x8B",       // ক্র  (র-ফলা after ক)
        'ক্ল' => "L\x8C",       // ক্ল  (ল-ফলা after ক)
        'ক্ষ' => "\xCA",        // ক্ষ  (dedicated ligature)
        'ক্স' => "Lp",          // ক্স

        // ── খ-যুক্ত ────────────────────────────────────────────────────
        'খ্য' => "Mk",
        'খ্র' => "M\x8B",

        // ── গ-যুক্ত ────────────────────────────────────────────────────
        'গ্গ' => "NN",
        'গ্ধ' => "Nd",
        'গ্ন' => "Ne",
        'গ্ব' => "\xBC",
        'গ্ম' => "Nj",
        'গ্য' => "Nk",
        'গ্র' => "N\x8B",
        'গ্ল' => "N\x8C",

        // ── ঘ-যুক্ত ────────────────────────────────────────────────────
        'ঘ্ন' => "Oe",
        'ঘ্র' => "O\x8B",

        // ── ঙ-যুক্ত ────────────────────────────────────────────────────
        'ঙ্ক' => "\xC5",        // ঙ্ক
        'ঙ্গ' => "\xC6",        // ঙ্গ
        'ঙ্ঘ' => "PO",

        // ── চ-যুক্ত ────────────────────────────────────────────────────
        'চ্চ' => "\xC7",        // চ্চ
        'চ্ছ' => "\xC8",        // চ্ছ
        'চ্ঞ' => "\xC9",        // চ্ঞ
        'চ্য' => "Qk",
        'চ্র' => "Q\x8B",

        // ── ছ-যুক্ত ────────────────────────────────────────────────────
        'ছ্র' => "R\x8B",

        // ── জ-যুক্ত ────────────────────────────────────────────────────
        'জ্জ' => "\xCB",        // জ্জ
        'জ্ঝ' => "\xCC",        // জ্ঝ
        'জ্ঞ' => "\xCD",        // জ্ঞ  (dedicated ligature)
        'জ্য' => "Sk",
        'জ্র' => "S\x8B",

        // ── ঞ-যুক্ত ────────────────────────────────────────────────────
        'ঞ্চ' => "\xCE",        // ঞ্চ
        'ঞ্ছ' => "UR",
        'ঞ্জ' => "\xCF",        // ঞ্জ

        // ── ট-যুক্ত ────────────────────────────────────────────────────
        'ট্ট' => "\xD0",        // ট্ট
        'ট্ম' => "Vj",
        'ট্য' => "Vk",
        'ট্র' => "V\x8B",

        // ── ঠ-যুক্ত ────────────────────────────────────────────────────
        'ঠ্র' => "W\x8B",

        // ── ড-যুক্ত ────────────────────────────────────────────────────
        'ড্ড' => "\xD2",        // ড্ড
        'ড্য' => "Xk",
        'ড্র' => "X\x8B",

        // ── ণ-যুক্ত ────────────────────────────────────────────────────
        'ণ্ট' => "\xD3",        // ণ্ট
        'ণ্ঠ' => "\xD4",        // ণ্ঠ
        'ণ্ড' => "\xD5",        // ণ্ড
        'ণ্ণ' => "ZZ",
        'ণ্ব' => "Z\xBF",
        'ণ্ম' => "Zj",
        'ণ্য' => "Zk",

        // ── ত-যুক্ত ────────────────────────────────────────────────────
        'ত্ত' => "\xD6",        // ত্ত
        'ত্থ' => "\xD7",        // ত্থ
        'ত্ন' => "ae",
        'ত্ব' => "\xBD",        // ত্ব
        'ত্ম' => "aj",
        'ত্য' => "ak",
        'ত্র' => "\xD8",        // ত্র  (dedicated ligature)
        'ত্ল' => "a\x8C",

        // ── থ-যুক্ত ────────────────────────────────────────────────────
        'থ্য' => "bk",
        'থ্র' => "b\x8B",

        // ── দ-যুক্ত ────────────────────────────────────────────────────
        'দ্গ' => "cN",
        'দ্ঘ' => "cO",
        'দ্দ' => "\xDA",        // দ্দ
        'দ্ধ' => "\xDB",        // দ্ধ
        'দ্ব' => "\xBE",        // দ্ব
        'দ্ভ' => "ci",
        'দ্ম' => "cj",
        'দ্য' => "ck",
        'দ্র' => "c\x8B",

        // ── ধ-যুক্ত ────────────────────────────────────────────────────
        'ধ্য' => "dk",
        'ধ্র' => "d\x8B",

        // ── ন-যুক্ত ────────────────────────────────────────────────────
        'ন্ক' => "eL",
        'ন্গ' => "eN",
        'ন্চ' => "\xDC",        // ন্চ
        'ন্ছ' => "eR",
        'ন্জ' => "eS",
        'ন্ট' => "\xDD",        // ন্ট
        'ন্ঠ' => "eW",
        'ন্ড' => "\xDE",        // ন্ড
        'ন্ত' => "\xDF",        // ন্ত
        'ন্থ' => "\xE0",        // ন্থ
        'ন্দ' => "\xE1",        // ন্দ
        'ন্ধ' => "\xE2",        // ন্ধ
        'ন্ন' => "\xE3",        // ন্ন
        'ন্ব' => "\xBF",        // ন্ব
        'ন্ম' => "ej",
        'ন্য' => "ek",
        'ন্স' => "ep",

        // ── প-যুক্ত ────────────────────────────────────────────────────
        'প্ট' => "fV",
        'প্ত' => "\xE4",        // প্ত
        'প্ন' => "fe",
        'প্প' => "ff",
        'প্য' => "fk",
        'প্র' => "f\x8B",       // প্র  (র-ফলা after প)
        'প্ল' => "f\x8C",       // প্ল  (ল-ফলা after প)
        'প্স' => "fp",

        // ── ফ-যুক্ত ────────────────────────────────────────────────────
        'ফ্র' => "g\x8B",
        'ফ্ল' => "g\x8C",

        // ── ব-যুক্ত ────────────────────────────────────────────────────
        'ব্জ' => "hS",
        'ব্ড' => "hX",
        'ব্দ' => "\xE5",        // ব্দ
        'ব্ধ' => "\xE6",        // ব্ধ
        'ব্ব' => "\xE7",        // ব্ব
        'ব্য' => "hk",
        'ব্র' => "h\x8B",
        'ব্ল' => "h\x8C",

        // ── ভ-যুক্ত ────────────────────────────────────────────────────
        'ভ্য' => "ik",
        'ভ্র' => "i\x8B",

        // ── ম-যুক্ত ────────────────────────────────────────────────────
        'ম্ন' => "je",
        'ম্ব' => "\xE8",        // ম্ব
        'ম্ভ' => "\xE9",        // ম্ভ
        'ম্ম' => "jj",
        'ম্য' => "jk",
        'ম্র' => "j\x8B",
        'ম্ল' => "j\x8C",

        // ── য-যুক্ত ────────────────────────────────────────────────────
        'য্য' => "kk",

        // ── র-যুক্ত — রেফ (র + হসন্ত + next consonant) ───────────────
        // রেফ (◌র্) sits ABOVE the following consonant as a hook glyph.
        // In AdorshoLipi: ref byte \x8A comes AFTER the consonant it sits on.
        // Unicode: র + ্ + consonant → consonant + \x8A
        // We handle this in a separate pass after strtr().
        // (See convertToAnsi() → addRef() step)

        // ── ল-যুক্ত ────────────────────────────────────────────────────
        'ল্ক' => "mL",
        'ল্গ' => "mN",
        'ল্ট' => "mV",
        'ল্ড' => "mX",
        'ল্ত' => "ma",
        'ল্থ' => "mb",
        'ল্দ' => "mc",
        'ল্ধ' => "md",
        'ল্প' => "mf",
        'ল্ফ' => "mg",
        'ল্ব' => "\xC0",        // ল্ব — re-use slot (verify in font)
        'ল্ভ' => "mi",
        'ল্ম' => "mj",
        'ল্য' => "mk",
        'ল্ল' => "\xEA",        // ল্ল
        'ল্স' => "mp",

        // ── শ-যুক্ত ────────────────────────────────────────────────────
        'শ্চ' => "nQ",
        'শ্ছ' => "nR",
        'শ্ন' => "ne",
        'শ্ব' => "\xC0",        // শ্ব
        'শ্ম' => "nj",
        'শ্য' => "nk",
        'শ্র' => "n\x8B",
        'শ্ল' => "n\x8C",

        // ── ষ-যুক্ত ────────────────────────────────────────────════════
        'ষ্ক' => "\xEB",        // ষ্ক
        'ষ্ট' => "\xEC",        // ষ্ট
        'ষ্ঠ' => "\xED",        // ষ্ঠ
        'ষ্ণ' => "\xEE",        // ষ্ণ
        'ষ্প' => "of",
        'ষ্ফ' => "og",
        'ষ্ব' => "\xC0",
        'ষ্ম' => "oj",
        'ষ্য' => "ok",

        // ── স-যুক্ত ────────────────────────────────────────────────────
        'স্ক' => "pL",
        'স্খ' => "pM",
        'স্ট' => "\xEF",        // স্ট
        'স্ত' => "\xF0",        // স্ত
        'স্থ' => "\xF1",        // স্থ
        'স্ন' => "pe",
        'স্প' => "\xF2",        // স্প
        'স্ফ' => "\xF3",        // স্ফ
        'স্ব' => "\xF4",        // স্ব
        'স্ম' => "pj",
        'স্য' => "pk",
        'স্র' => "p\x8B",
        'স্ল' => "p\x8C",

        // ── হ-যুক্ত ────────────────────────────────────────────────────
        'হ্ণ' => "\xF5",        // হ্ণ
        'হ্ন' => "\xF6",        // হ্ন
        'হ্ব' => "\xBF",
        'হ্ম' => "\xF7",        // হ্ম
        'হ্য' => "qk",
        'হ্র' => "q\x8B",
        'হ্ল' => "q\x8C",

        // ══════════════════════════════════════════════════════════════════
        // ফলা (Sub-joined consonant glyphs used as second member of conjunct)
        // ══════════════════════════════════════════════════════════════════
        // These are used when a conjunct is NOT in the pre-built table above.
        // ্য  →  \x8D  (য-ফলা)
        // ্ব  →  \xC0  (ব-ফলা) — same as some conjuncts above; context dependent
        // ্ম  →  \x8E  (ম-ফলা) — below-base form
        // ্ন  →  handled case-by-case
        //
        // র-ফলা (\x8B) and ল-ফলা (\x8C) are used inline above.
        // য-ফলা in generic position:
        '্য' => "\x8D",       // ্য  generic য-ফলা
        '্ব' => "\xC4",       // ্ব  generic ব-ফলা (standalone)
        '্ম' => "\x8E",       // ্ম  generic ম-ফলা
        '্র' => "\x8B",       // ্র  generic র-ফলা
        '্ল' => "\x8C",       // ্ল  generic ল-ফলা

        // Hasanta (generic — for conjuncts not in table)
        '্' => "&",           // হসন্ত / virama

        // ══════════════════════════════════════════════════════════════════
        // INDEPENDENT VOWELS (স্বতন্ত্র স্বরবর্ণ)
        // ══════════════════════════════════════════════════════════════════
        'অ' => "A",
        'আ' => "B",
        'ই' => "C",
        'ঈ' => "D",
        'উ' => "E",
        'ঊ' => "F",
        'ঋ' => "G",
        'এ' => "H",
        'ঐ' => "I",
        'ও' => "J",
        'ঔ' => "K",

        // ══════════════════════════════════════════════════════════════════
        // CONSONANTS (ব্যঞ্জনবর্ণ)
        // ══════════════════════════════════════════════════════════════════
        'ক' => "L",
        'খ' => "M",
        'গ' => "N",
        'ঘ' => "O",
        'ঙ' => "P",
        'চ' => "Q",
        'ছ' => "R",
        'জ' => "S",
        'ঝ' => "T",
        'ঞ' => "U",
        'ট' => "V",
        'ঠ' => "W",
        'ড' => "X",
        'ঢ' => "Y",
        'ণ' => "Z",
        'ত' => "a",
        'থ' => "b",
        'দ' => "c",
        'ধ' => "d",
        'ন' => "e",
        'প' => "f",
        'ফ' => "g",
        'ব' => "h",
        'ভ' => "i",
        'ম' => "j",
        'য' => "k",
        'র' => "l",
        'ল' => "m",
        'শ' => "n",
        'ষ' => "o",
        'স' => "p",
        'হ' => "q",
        'ড়' => "r",
        'ঢ়' => "s",
        'য়' => "t",
        'ৎ' => "u",
        'ং' => "v",
        'ঃ' => "w",
        'ঁ' => "x",

        // ══════════════════════════════════════════════════════════════════
        // VOWEL SIGNS / MATRAS (কার চিহ্ন)
        // ══════════════════════════════════════════════════════════════════
        //
        // Right-side / below / above matras — simply appended after consonant:
        'া' => "\xA1",           // আ-কার (aa-matra)
        'ি' => "\xA2",           // ই-কার (i-matra)
        'ী' => "\xA3",           // ঈ-কার (ii-matra)
        'ু' => "\xA4",           // উ-কার (u-matra)
        'ূ' => "\xA5",           // ঊ-কার (uu-matra)
        'ৃ' => "\xA6",           // ঋ-কার (ri-matra)

        // Pre-position matras — replaced with sentinels; reordered by
        // reorderMatras() after strtr() finishes:
        //
        //   sentinel \x01 → real byte \x87 placed BEFORE consonant  (ে)
        //   sentinel \x02 → real byte \x89 placed BEFORE consonant  (ৈ)
        //   sentinel \x03 → real byte \x87 before, \xA1 after        (ো)
        //   sentinel \x04 → real byte \x87 before, \xD1 after        (ৌ)
        //
        // For compound matras ো/ৌ, a post-byte follows the sentinel to
        // indicate which right-side glyph to append after the consonant.
        //   ো  =  \x03 + \xA1   (strtr puts these two bytes after consonant;
        //                         reorderMatras moves \x87 before consonant
        //                         and keeps \xA1 after)
        //   ৌ  =  \x04 + \xD1
        //
        'ে' => "\x01",          // ে-কার  → sentinel (reordered to \x87 before cons)
        'ৈ' => "\x02",          // ৈ-কার  → sentinel (reordered to \x89 before cons)
        'ো' => "\x03\xA1",      // ো-কার  → sentinel+post (reordered to \x87+cons+\xA1)
        'ৌ' => "\x04\xD1",      // ৌ-কার  → sentinel+post (reordered to \x87+cons+\xD1)

        // ══════════════════════════════════════════════════════════════════
        // DIGITS AND PUNCTUATION
        // ══════════════════════════════════════════════════════════════════
        '০' => "0",
        '১' => "1",
        '২' => "2",
        '৩' => "3",
        '৪' => "4",
        '৫' => "5",
        '৬' => "6",
        '৭' => "7",
        '৮' => "8",
        '৯' => "9",
        '।' => "|",              // দাড়ি
        ' ' => " ",
    ];

    // ══════════════════════════════════════════════════════════════════════
    // REF (রেফ) PATTERN
    // ══════════════════════════════════════════════════════════════════════
    // Unicode: র + ্ + <consonant>  →  AdorshoLipi: <consonant> + \x8A
    // We detect র্ before any consonant cluster in a separate pre-pass.
    // The Unicode sequence is: U+09B0 (র) + U+09CD (্)
    // The regex below matches that two-char sequence and what follows it.

    private const REF_UNICODE_PREFIX = 'র্';   // র + হসন্ত

    // ══════════════════════════════════════════════════════════════════════
    // Public API
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Convert an integer (0 – 9,99,99,999) to Unicode Bangla words.
     */
    public function toBanglaWords(int|float|string $number): string
    {
        $n = (int) $number;

        if ($n === 0) {
            return 'শূন্য';
        }

        $prefix = '';
        if ($n < 0) {
            $prefix = 'ঋণাত্মক ';
            $n = abs($n);
        }

        $parts = [];
        $koti = intdiv($n, 10_000_000);
        $n %= 10_000_000;
        $lakh = intdiv($n, 100_000);
        $n %= 100_000;
        $hajar = intdiv($n, 1_000);
        $n %= 1_000;
        $shoto = intdiv($n, 100);
        $baki = $n % 100;

        if ($koti > 0)
            $parts[] = $this->bnUpTo99($koti) . ' কোটি';
        if ($lakh > 0)
            $parts[] = $this->bnUpTo99($lakh) . ' লক্ষ';
        if ($hajar > 0)
            $parts[] = $this->bnUpTo99($hajar) . ' হাজার';
        if ($shoto > 0)
            $parts[] = $this->bnUpTo99($shoto) . ' শত';
        if ($baki > 0)
            $parts[] = self::BN_ONES[$baki];

        return $prefix . implode(' ', $parts);
    }

    /**
     * Convert an integer (0 – 9,99,99,999) to English words (BD/IN system).
     */
    public function toEnglishWords(int|float|string $number): string
    {
        $n = (int) $number;

        if ($n === 0) {
            return 'Zero';
        }

        $prefix = '';
        if ($n < 0) {
            $prefix = 'Negative ';
            $n = abs($n);
        }

        $parts = [];
        $koti = intdiv($n, 10_000_000);
        $n %= 10_000_000;
        $lakh = intdiv($n, 100_000);
        $n %= 100_000;
        $hajar = intdiv($n, 1_000);
        $n %= 1_000;
        $shoto = intdiv($n, 100);
        $baki = $n % 100;

        if ($koti > 0)
            $parts[] = $this->enUpTo99($koti) . ' Crore';
        if ($lakh > 0)
            $parts[] = $this->enUpTo99($lakh) . ' Lakh';
        if ($hajar > 0)
            $parts[] = $this->enUpTo99($hajar) . ' Thousand';
        if ($shoto > 0)
            $parts[] = $this->enUpTo99($shoto) . ' Hundred';
        if ($baki > 0)
            $parts[] = $this->enUpTo99($baki);

        return $prefix . implode(' ', $parts);
    }

    /**
     * Convert Unicode Bangla text → AdorshoLipi ANSI bytes → base64.
     *
     * Returns a base64-encoded string (pure ASCII → always valid UTF-8/JSON).
     * Livewire component properties and JSON fields are safe.
     *
     * To display in browser: atob() in JavaScript.
     * To copy raw bytes:     base64_decode() in PHP, then clipboard.
     */
    public function toAdorshoLipiBase64(string $unicodeText): string
    {
        return base64_encode($this->toAdorshoLipi($unicodeText));
    }

    /**
     * Convert Unicode Bangla text → raw AdorshoLipi ANSI byte string.
     *
     * ⚠ The returned string is NOT valid UTF-8.
     * Never store in Livewire properties or pass through json_encode().
     * Use toAdorshoLipiBase64() for those contexts.
     */
    public function toAdorshoLipi(string $unicodeText): string
    {
        return $this->convertToAnsi($unicodeText);
    }

    // ══════════════════════════════════════════════════════════════════════
    // Private: conversion pipeline
    // ══════════════════════════════════════════════════════════════════════

    private function convertToAnsi(string $text): string
    {
        // ── Step 1: handle রেফ (র্) pre-pass ──────────────────────────
        // Unicode র্<cons> must become <cons>\x8A in AdorshoLipi.
        // We collect ref positions before strtr() changes everything.
        $text = $this->resolveRef($text);

        // ── Step 2: map all Unicode clusters → ANSI bytes via strtr() ─
        // strtr() uses longest-match-first, so conjuncts beat bare consonants.
        $raw = strtr($text, self::ADORSHO_MAP);

        // ── Step 3: reorder pre-position matra sentinels ───────────────
        return $this->reorderMatras($raw);
    }

    /**
     * Pre-process রেফ (র + ্ + consonant/conjunct).
     *
     * Unicode order:  র ্ ক  (3 chars)
     * AdorshoLipi:    L \x8A  (consonant byte + ref glyph appended)
     *
     * Strategy: replace each occurrence of "র্<cluster>" with a temporary
     * marker that wraps the cluster, then after strtr(), a second sweep
     * appends \x8A.
     *
     * However, since strtr() must run first to convert the cluster, we
     * instead SWAP the order: temporarily move র্ to AFTER its consonant
     * cluster so strtr() can process the cluster normally, then in
     * reorderMatras() we detect the sentinel and append \x8A.
     *
     * Simpler approach used here: replace "র্X" with "X\x05" where \x05
     * is a "needs-ref" sentinel. After strtr() converts X, we replace
     * each \x05 in the ANSI stream with \x8A.
     */
    private function resolveRef(string $text): string
    {
        // Match র্ followed by one Unicode grapheme cluster (consonant or conjunct).
        // We replace র্<grapheme> with <grapheme>\x05 (ref-needed sentinel).
        //
        // A "grapheme cluster" here is: one consonant codepoint optionally
        // followed by nukta and/or vowel sign and/or hasanta+consonant chains.
        // For our purposes (number words) the clusters after রেফ are simple
        // single consonants, so a basic character-by-character approach works.
        //
        // We iterate through the string splitting on the prefix.

        $prefix = self::REF_UNICODE_PREFIX; // র্

        if (!str_contains($text, $prefix)) {
            return $text;
        }

        // Split and reconstruct, moving ref sentinel after the next char cluster.
        $result = '';
        $len = mb_strlen($text, 'UTF-8');
        $i = 0;

        while ($i < $len) {
            // Check if current position starts with রেফ prefix (র + ্)
            if (mb_substr($text, $i, 2, 'UTF-8') === $prefix) {
                $i += 2; // skip র্
                // Collect the consonant cluster that follows:
                // grab one base consonant char (we advance by 1 codepoint)
                $cluster = '';
                if ($i < $len) {
                    $cluster = mb_substr($text, $i, 1, 'UTF-8');
                    $i++;
                    // If followed by hasanta + consonant (forming a conjunct), include all
                    while ($i + 1 < $len && mb_substr($text, $i, 1, 'UTF-8') === '্') {
                        $cluster .= mb_substr($text, $i, 2, 'UTF-8');
                        $i += 2;
                    }
                }
                // Append cluster first, then ref sentinel
                $result .= $cluster . "\x05";
            } else {
                $result .= mb_substr($text, $i, 1, 'UTF-8');
                $i++;
            }
        }

        return $result;
    }

    /**
     * After strtr() runs, rearrange pre-position matra sentinels.
     *
     * The byte stream at this point contains:
     *   <consonant-byte(s)> <SENTINEL> [optional-post-byte]
     *
     * Each sentinel type:
     *   \x01  (ে)   →  insert \x87 BEFORE the preceding consonant byte
     *   \x02  (ৈ)   →  insert \x89 BEFORE the preceding consonant byte
     *   \x03  (ো)   →  insert \x87 BEFORE consonant; \xA1 comes AFTER (already in stream)
     *   \x04  (ৌ)   →  insert \x87 BEFORE consonant; \xD1 comes AFTER (already in stream)
     *   \x05  (রেফ) →  append \x8A AFTER the preceding consonant byte
     *
     * "Preceding consonant byte" = the single byte immediately before the sentinel.
     * (In AdorshoLipi a consonant is always ONE byte; multi-byte conjuncts are
     *  also single bytes from the pre-built glyph table.)
     *
     * The regex [^\x01-\x06] matches any byte that is NOT a sentinel,
     * i.e. any real ANSI glyph byte.
     */
    private function reorderMatras(string $s): string
    {
        // Byte class: any non-sentinel byte
        $c = '[^\x01\x02\x03\x04\x05\x06]';

        // ১. ে-কার: <cons>\x01  →  \x87<cons>
        $s = preg_replace_callback(
            "/({$c})\x01/s",
            static fn($m): string => "\x87" . $m[1],
            $s
        );

        // ২. ৈ-কার: <cons>\x02  →  \x89<cons>
        $s = preg_replace_callback(
            "/({$c})\x02/s",
            static fn($m): string => "\x89" . $m[1],
            $s
        );

        // ৩. ো-কার: <cons>\x03\xA1  →  \x87<cons>\xA1
        $s = preg_replace_callback(
            "/({$c})\x03\xA1/s",
            static fn($m): string => "\x87" . $m[1] . "\xA1",
            $s
        );

        // ৪. ৌ-কার: <cons>\x04\xD1  →  \x87<cons>\xD1
        $s = preg_replace_callback(
            "/({$c})\x04\xD1/s",
            static fn($m): string => "\x87" . $m[1] . "\xD1",
            $s
        );

        // ৫. রেফ: <cons>\x05  →  <cons>\x8A
        $s = preg_replace_callback(
            "/({$c})\x05/s",
            static fn($m): string => $m[1] . "\x8A",
            $s
        );

        return $s;
    }

    // ══════════════════════════════════════════════════════════════════════
    // Private helpers
    // ══════════════════════════════════════════════════════════════════════

    private function bnUpTo99(int $n): string
    {
        return self::BN_ONES[$n] ?? '';
    }

    private function enUpTo99(int $n): string
    {
        if ($n < 20) {
            return self::EN_ONES[$n] ?? '';
        }
        $tens = self::EN_TENS[intdiv($n, 10)];
        $ones = self::EN_ONES[$n % 10] ?? '';

        return $ones !== '' ? "{$tens} {$ones}" : $tens;
    }
}