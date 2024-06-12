ARG TAG=20-alpine
FROM node:$TAG

RUN npm install -g npm@^10.0.0
RUN npm install -g pnpm@^9.3.0

WORKDIR /app/buildchain/

CMD ["run build"]

ENTRYPOINT ["pnpm"]
