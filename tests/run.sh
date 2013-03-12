dir=$(cd "$(dirname "$0")";pwd);

cd $dir
../vendor/bin/phpunit ./unit &&
../vendor/bin/phpunit ./integration
