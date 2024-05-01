until nc -v $LARAVEL_RABBITMQ_HOST 5672 > /dev/null 2>&1; do
    code=$?
    if [ $code -ne 0 ]; then
        echo "rabbitmq not available! error code: $code"
        sleep 3
    fi
done
