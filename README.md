[![Build Status](https://travis-ci.org/francis94c/ci-rest.svg?branch=master)](https://travis-ci.org/francis94c/ci-rest) [![Coverage Status](https://coveralls.io/repos/github/francis94c/ci-rest/badge.svg?branch=master)](https://coveralls.io/github/francis94c/ci-rest?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/francis94c/ci-rest/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/francis94c/ci-rest/?branch=master)

# ci-rest
A REST API Library/Framework for Code Igniter.

This library is currently in progress together with another project that depends on it, so as to enable me design it in such a way that cuts through various applications.

I'll be documenting along the way, before the full code finally makes it here for its first release.

# Synopsis

This library makes it possible to develop REST servers in Code Igniter, having your code responsible for validating authorizations in one place and the codes for actually accessing resources in another place.

This allows you write uniform maintainable server-side code.

Think of it as a library that validates clients as per your specifications, and let's your main code run if authorization passes.

You also get to take actions or customize responses when authorization fails.

This also takes care of API Rate Limiting.

A very detailed write up of how to install and use this library follows below.


### Installation ###
Download and Install Splint from https://splint.cynobit.com/downloads/splint and run the below from the root of your Code Igniter project.
```bash
splint install francis94c/ci-rest
```
