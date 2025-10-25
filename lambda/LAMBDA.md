# AWS Lambda Deployment Guide

This document explains how to package and deploy the fetch-meditation library as an AWS Lambda function.

## Building the Docker Image

```bash
docker build -t fetch-meditation-lambda .
```

## Local Testing

### Using Docker Compose
```bash
docker-compose up
```

### Testing with curl
```bash
# Test JFT in English
curl -X POST "http://localhost:9000/2015-03-31/functions/function/invocations" \
  -d '{"type": "jft", "language": "english"}'

# Test SPAD in German
curl -X POST "http://localhost:9000/2015-03-31/functions/function/invocations" \
  -d '{"type": "spad", "language": "german"}'

# Test with timezone
curl -X POST "http://localhost:9000/2015-03-31/functions/function/invocations" \
  -d '{"type": "jft", "language": "spanish", "timezone": "America/New_York"}'
```

## Deployment to AWS Lambda

### 1. Create ECR Repository
```bash
aws ecr create-repository --repository-name fetch-meditation-lambda --region us-east-1
```

### 2. Authenticate Docker to ECR
```bash
aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin <account-id>.dkr.ecr.us-east-1.amazonaws.com
```

### 3. Tag and Push Image
```bash
docker tag fetch-meditation-lambda:latest <account-id>.dkr.ecr.us-east-1.amazonaws.com/fetch-meditation-lambda:latest
docker push <account-id>.dkr.ecr.us-east-1.amazonaws.com/fetch-meditation-lambda:latest
```

### 4. Create Lambda Function
```bash
aws lambda create-function \
  --function-name fetch-meditation \
  --package-type Image \
  --code ImageUri=<account-id>.dkr.ecr.us-east-1.amazonaws.com/fetch-meditation-lambda:latest \
  --role arn:aws:iam::<account-id>:role/<lambda-execution-role> \
  --timeout 30 \
  --memory-size 512
```

### 5. Test Lambda Function
```bash
aws lambda invoke \
  --function-name fetch-meditation \
  --payload '{"type": "jft", "language": "english"}' \
  response.json

cat response.json
```

## API Gateway Integration (Optional)

To expose the Lambda function as an HTTP API:

```bash
# Create HTTP API
aws apigatewayv2 create-api \
  --name fetch-meditation-api \
  --protocol-type HTTP \
  --target arn:aws:lambda:us-east-1:<account-id>:function:fetch-meditation

# Grant API Gateway permission to invoke Lambda
aws lambda add-permission \
  --function-name fetch-meditation \
  --statement-id apigateway-invoke \
  --action lambda:InvokeFunction \
  --principal apigateway.amazonaws.com
```

## Request/Response Format

### Request
```json
{
  "type": "jft",           // "jft" or "spad"
  "language": "english",   // Language name (lowercase)
  "timezone": "UTC"        // Optional timezone
}
```

### Response (Success)
```json
{
  "statusCode": 200,
  "body": "{\"success\": true, \"data\": {...}}"
}
```

### Response (Error)
```json
{
  "statusCode": 500,
  "body": "{\"success\": false, \"error\": \"Error message\"}"
}
```

## Supported Languages

**JFT**: English, French, German, Italian, Japanese, Portuguese, Russian, Spanish, Swedish

**SPAD**: English, German

## Cost Optimization

- **Memory**: Start with 512 MB, adjust based on performance
- **Timeout**: 30 seconds should be sufficient for most requests
- **Architecture**: Consider using ARM64 (Graviton2) for lower cost
- **Provisioned Concurrency**: Only if you need consistent sub-second response times

## Monitoring

View logs with CloudWatch:
```bash
aws logs tail /aws/lambda/fetch-meditation --follow
```
