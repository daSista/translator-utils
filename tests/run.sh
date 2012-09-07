dir=$(cd "$(dirname "$0")";pwd);

cd $dir
phpunit ./unit && phpunit ./integration
