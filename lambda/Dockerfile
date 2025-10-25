FROM public.ecr.aws/lambda/provided:al2023

RUN dnf install -y \
    php \
    php-cli \
    php-xml \
    php-mbstring \
    php-json \
    unzip \
    zip \
    && dnf clean all

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR ${LAMBDA_TASK_ROOT}
COPY composer.json composer.lock* ./
COPY src ./src
COPY lambda/lambda-handler.php ./

RUN composer install --no-dev --optimize-autoloader --no-interaction

# bootstrap script
RUN cat > /var/runtime/bootstrap <<'EOF' && chmod +x /var/runtime/bootstrap
#!/bin/sh
set -euo pipefail

_HANDLER="lambda-handler.php"

# Get the next event from the Lambda runtime API
while true; do
    HEADERS="$(mktemp)"
    EVENT_DATA=$(curl -sS -LD "$HEADERS" -X GET "http://${AWS_LAMBDA_RUNTIME_API}/2018-06-01/runtime/invocation/next")
    REQUEST_ID=$(grep -Fi Lambda-Runtime-Aws-Request-Id "$HEADERS" | tr -d '[:space:]' | cut -d: -f2)

    # Execute the handler function
    RESPONSE=$(echo "$EVENT_DATA" | php -r '
        require "'${LAMBDA_TASK_ROOT}/${_HANDLER}'";
        $event = json_decode(file_get_contents("php://stdin"), true);
        $response = handler($event);
        echo json_encode($response);
    ')

    # Send the response back to the Lambda runtime API
    curl -X POST "http://${AWS_LAMBDA_RUNTIME_API}/2018-06-01/runtime/invocation/$REQUEST_ID/response" -d "$RESPONSE"
done
EOF

CMD [ "lambda-handler.handler" ]
