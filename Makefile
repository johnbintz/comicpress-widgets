FILES := $(shell find . -name '*.php' -o -name '*.inc')

test-coverage : $(FILES)
	phpunit --coverage-html coverage --syntax-check test

