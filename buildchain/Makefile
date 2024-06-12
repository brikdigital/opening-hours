MAJOR_VERSION?=4
TAG?=20-alpine
CONTAINER?=$(shell basename $(dir $(CURDIR)))-v${MAJOR_VERSION}-buildchain
DEV_PORT?=300${MAJOR_VERSION}
IMAGE_INFO=$(shell docker image inspect $(CONTAINER):$(TAG))
IMAGE_NAME=${CONTAINER}:${TAG}
DOCKER_NETWORK?=ddev-plugcraft4_default
DOCKER_RUN=docker container run --rm -it --network "${DOCKER_NETWORK}" -v "${CURDIR}"/../:/app
#DOCKER_RUN=docker container run --rm -it -v "${CURDIR}"/../:/app

.PHONY: build clean dev image-build image-check pnpm ssh

# Perform a dist build via pnpm run build
build: image-check
	${DOCKER_RUN} --name ${CONTAINER}-$@ ${IMAGE_NAME} run build
# Remove node_modules/
clean:
	rm -rf node_modules/
# Run the development server via pnpm run dev
dev: image-check
	${DOCKER_RUN} --name ${CONTAINER}-$@ -e DEV_PORT="${DEV_PORT}" -p ${DEV_PORT}:${DEV_PORT} ${IMAGE_NAME} run dev
# Build the Docker image & run pnpm install
image-build:
	docker build . -t ${IMAGE_NAME} --build-arg TAG=${TAG} --no-cache
	${DOCKER_RUN} --name ${CONTAINER}-$@ ${IMAGE_NAME} install
# Ensure the image has been created
image-check:
ifeq ($(IMAGE_INFO), [])
image-check: image-build
endif
# Run the passed in pnpm command
pnpm: image-check
	${DOCKER_RUN} --name ${CONTAINER}-$@ ${IMAGE_NAME} $(filter-out $@,$(MAKECMDGOALS)) $(MAKEFLAGS)
# Open a shell inside of the container
ssh: image-check
	${DOCKER_RUN} --name ${CONTAINER}-$@ --entrypoint=/bin/sh ${IMAGE_NAME}
%:
	@:
# ref: https://stackoverflow.com/questions/6273608/how-to-pass-argument-to-makefile-from-command-line
