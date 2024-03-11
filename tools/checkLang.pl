#! /usr/bin/perl -w
#
# checkLang.pl
#
# This tool checks the language files of Emaj-web. It checks that:
#   - all strings used in the php code exist in the english.php reference language file;
#   - all strings from the english.php language file are used in the php code;
#	- all strings from the english.php language file are defined only once;
#   - all alternate language files have the same strings as the english.php file and at the same line in the file.
#
# The tool is placed into the tool directory but must be executed from the project root directory:
#   ./tool/checkLang.pl
#
use warnings;
use strict;

# Lists and hashes.
my @usedStrings;			# 
my @englishLines;			# lines of the english.php file
my %englishStrings;			# hash table indicating the line number of each string id
my %englishStringsUsage;	# hash table indicating the number of times the string id is used in the code

# Counters.
my $nbTotalErrors = 0;

# Load all used strings from the Emaj_web project into the @usedStrings list.
# Strings located in php comments are recorded as well.
# Each recorded line is formated as <file>:<line_number>:lang['<string>']
sub loadStrings {
	my @strings;
	my $nbUsedString = 0;

	print "===== Collecting strings usage in php code =====\n";

	my $cmd = "grep -rPno 'lang\\[.*?\\]' *";
	@strings = `$cmd`;
	foreach my $str (@strings) {
		if ($str !~ /^lang|tools/) {		# Discard the files from the ./lang and ./tools directories.
			chomp $str;
# Add a few virtual string usages to fit the special case of string ids not set as literals.
			if ($str =~ /^(emajgroups.php:\d+:lang\['emajgroupnot)'\.\$langMsgSuffix\]/) {
				push(@usedStrings, "$1started']");
				push(@usedStrings, "$1stopped']");
				$nbUsedString = $nbUsedString + 2;
				next;
			}
			if ($str =~ /^(emajgroups.php:\d+:lang\['emajgroupsnot)'\.\$langMsgSuffix\]/) {
#print "$str => $1started']\n";
				push(@usedStrings, "$1started']");
				push(@usedStrings, "$1stopped']");
				$nbUsedString = $nbUsedString + 2;
				next;
			}
			if ($str =~ /^(emajgroups.php:\d+:lang\[)\$parts\[0\]/) {
				push(@usedStrings, "$1'emajlogsession']");
				push(@usedStrings, "$1'emajgroupcreate']");
				push(@usedStrings, "$1'emajgroupdrop']");
				push(@usedStrings, "$1'emajdeletedlogsessions']");
				$nbUsedString = $nbUsedString + 4;
				next;
			}
# Usual case.
			push(@usedStrings, $str);
			$nbUsedString++;
		}
	}
	print "    Number of strings in non lang/ php files = $nbUsedString\n";
	return;
}

# Load all strings from lang/english file into the @englishLines list.
# Strings located in php line comments are NOT recorded.
# Each recorded line is formated as <line_number>:<full_line_including_$lang['...']>.
sub loadEnglishStrings {
	my $nbEnglishString = 0;
	my $nbDuplicateEnglishString = 0;

	print "===== Loading and checking strings from lang/english.php =====\n";

	my $cmd = "grep -Pn '^\\s*\\\$lang\\[.*?\\]' lang/english.php";
	@englishLines = `$cmd`;

	foreach my $str (@englishLines) {
		my ($lineNumber, $stringId) = ($str =~ /(.*?):.*?lang\['(.*?)'\]/);
		if (! defined($englishStrings{$stringId})) {
			$englishStrings{$stringId} = $lineNumber;
			$englishStringsUsage{$stringId} = 0;
			$nbEnglishString++;
		} else {
			print "*** In the lang/english.php file, the string '$stringId' in line $lineNumber is already defined in line $englishStrings{$stringId}\n";
			$nbDuplicateEnglishString++;
		}
	}

	print "    Number of strings in lang/english.php file = $nbEnglishString\n";
	print "    Number of duplicate strings in lang/english.php file = $nbDuplicateEnglishString\n";
	$nbTotalErrors += $nbDuplicateEnglishString;
	return;
}

# Check that all strings used in php code are present in the english.php file.
sub checkMissingEnglishStrings {
	my $nbMissingEnglishString = 0;
	foreach my $str (@usedStrings) {
		chomp $str;
		next if ($str =~ /lang\['str'.\$subject\]/);						# Discarded a special case from Misc.php (the code should never be reached).
		my ($file, $lineNumber, $stringId) = ($str =~ /^(.*?):(.*?):lang\['(.*?)'\]/);
		if (! defined($file)) {
			print "*** Error while splitting the record $str. (May be there is a special case to code in the loadStrings() function)\n";
		} else {
			if (! defined($englishStrings{$stringId})) {
				print "*** In the $file file, the string '$stringId' used in line $lineNumber does not exist in the english.php file\n";
				$nbMissingEnglishString++;
			} else {
				$englishStringsUsage{$stringId}++;
			}
		}
	}
	print "    Number of missing strings in lang/english.php file = $nbMissingEnglishString\n";
	$nbTotalErrors += $nbMissingEnglishString;
	return;
}

# Check that all strings defined in english.php are really needed.
sub checkUselessEnglishString {
	my $nbUselessEnglishString = 0;
	foreach my $str (@englishLines) {
		my ($lineNumber, $stringId) = ($str =~ /(.*?):.*?lang\['(.*?)'\]/);
		if ($englishStringsUsage{$stringId} == 0) {
			print "*** In the lang/english.php file, the string '$stringId' in line $lineNumber is never used in the code\n";
			$nbUselessEnglishString++;
		}
	}
	print "    Number of useless strings in lang/english.php file = $nbUselessEnglishString\n";
	$nbTotalErrors += $nbUselessEnglishString;
	return;
}

# Check alternate language files present in the lang directory.
sub checkOtherLanguageFiles {
	my $line;
	my $listStartFound = 0;
	my $fileName;

# Read the lang/translations.php file in order to get the known language files.
	open (LANGFILE, "lang/translations.php")
		|| die ("Error while opening lang/translations.php file\n");
	while (<LANGFILE>){
		$line = $_;
# Analyze the $appLangFiles array description te extract the languages. It looks like:
#        $appLangFiles = array(
#                'english' => 'English',
#                'french' => 'Français'
#        );
		if (! $listStartFound && $line =~ /appLangFiles = array/) {
			$listStartFound = 1;
			next;
		}
		if ($listStartFound) {
			last if ($line =~ /^\s*\);/);
			if ($line =~ /^\s*'(.*?)'\s*=>/) {
				if ($1 ne 'english') {
					$fileName = "lang/$1.php";
					checkOneLanguageFile($fileName);
				}
			} else {
				die "Error while reading the translations.php file\n";
			}
		}
	}
	close (LANGFILE);
	return;
}

# Check a single alternate language file.
sub checkOneLanguageFile {
	my ($fileName) = @_;
	my %otherStrings;
	my %otherStringsUsage;

	print "===== Loading and checking strings from $fileName =====\n";
	my $nbOtherString = 0;
	my $nbDuplicateOtherString = 0;
	my $nbMissingOtherString = 0;
	my $nbUselessOtherString = 0;
	my $startOrderDiffLineNumber = 0;

# Read the file.
	my $cmd = "grep -Pn '^\\s*\\\$lang\\[.*?\\]' $fileName";
	my @lines = `$cmd`;

# Look for useless and duplicate strings.
	foreach my $str (@lines) {
		my ($lineNumber, $stringId) = ($str =~ /(.*?):.*?lang\['(.*?)'\]/);
		$nbOtherString++;
		if (! defined($otherStrings{$stringId})) {
			$otherStrings{$stringId} = $lineNumber;
			$otherStringsUsage{$stringId} = 0;
			$nbOtherString++;
		} else {
			print "*** In the $fileName file, the string '$stringId' in line $lineNumber is already defined in line $otherStrings{$stringId}\n";
			$nbDuplicateOtherString++;
		}
		if (! defined($englishStrings{$stringId})) {
			print "*** The string '$stringId', defined in $fileName at line $lineNumber, has not been found in lang/english.php\n";
			$nbUselessOtherString++;
			$startOrderDiffLineNumber = $lineNumber if ($startOrderDiffLineNumber == 0);
		} else {
# Detect order differences with the english strings.
			if ($startOrderDiffLineNumber == 0 && $englishStrings{$stringId} != $lineNumber) {
				$startOrderDiffLineNumber = $lineNumber;
			}
		}
	}

# Look for missing strings.
	foreach my $str (@englishLines) {
		my ($lineNumber, $stringId) = ($str =~ /(.*?):.*?lang\['(.*?)'\]/);
		if (! defined($otherStrings{$stringId})) {
			print "*** The string '$stringId', defined in lang/english.php at line $lineNumber, is missing in $fileName\n";
			$nbMissingOtherString++;
		}
	}

# Display the checks summary for this alternate language.
	print "    Number of strings in $fileName file = $nbOtherString\n";
	print "    Number of duplicate strings in $fileName file = $nbDuplicateOtherString\n";
	print "    Number of missing strings in $fileName file = $nbMissingOtherString\n";
	print "    Number of useless strings in $fileName file = $nbUselessOtherString\n";
	if ($startOrderDiffLineNumber > 0) {
		print "/!\\ $fileName lines order differs from the lang/english.php lines order starting at line $startOrderDiffLineNumber\n";
	}
	$nbTotalErrors += $nbDuplicateOtherString + $nbMissingOtherString + $nbUselessOtherString;

	return;
}

# Main
print "========== checkLang.pl ===========\n";

loadStrings();

loadEnglishStrings();
checkMissingEnglishStrings();
checkUselessEnglishString();

checkOtherLanguageFiles();

if ($nbTotalErrors > 0) {
	print "\n$nbTotalErrors errors detected.\n";
} else {
	print "\nNo error detected.\n";
}
