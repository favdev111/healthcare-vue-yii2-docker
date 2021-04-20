module.exports = {
	"rules": {
		"block-no-empty": true,
		"color-no-invalid-hex": true,
		"comment-empty-line-before": [ "always", {
			"ignore": ["stylelint-commands", "between-comments"],
		} ],
		"declaration-colon-space-after": "always",
		"indentation": [4, {
			"except": ["value"]
		}],
		"max-empty-lines": 2,
		"rule-nested-empty-line-before": [ "always", {
			"except": ["first-nested"],
			"ignore": ["after-comment"],
		} ],
		"string-no-newline": true,
    	"unit-case": "lower",
    	"no-missing-end-of-source-newline": true,
		"unit-whitelist": ["em", "rem", "%", "s", "deg", 'vh', 'vw', 'px']
	}
}
