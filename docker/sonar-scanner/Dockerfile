# syntax=docker/dockerfile:1

FROM eclipse-temurin:21.0.12.1_1-jre-noble AS download

ARG SONAR_SCANNER_VERSION=8.1.0.6389

WORKDIR /tmp

RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && \
    apt-get install -y --no-install-recommends ca-certificates curl unzip && \
    curl --fail --show-error --silent --location \
      "https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-${SONAR_SCANNER_VERSION}.zip" \
      --output /tmp/sonar-scanner.zip && \
    unzip -q /tmp/sonar-scanner.zip -d /opt && \
    mv "/opt/sonar-scanner-${SONAR_SCANNER_VERSION}" /opt/sonar-scanner && \
    rm /tmp/sonar-scanner.zip

FROM eclipse-temurin:21.0.12.1_1-jre-noble AS runtime

LABEL org.opencontainers.image.source="https://github.com/SonarSource/sonar-scanner-cli"

COPY --from=download --chown=10001:10001 /opt/sonar-scanner /opt/sonar-scanner
COPY --chown=10001:10001 entrypoint.sh /usr/local/bin/dto-sonar-entrypoint

RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && \
    apt-get install -y --no-install-recommends git && \
    groupadd --gid 10001 sonar && \
    useradd --uid 10001 --gid 10001 --create-home --shell /usr/sbin/nologin sonar && \
    chmod 0755 /usr/local/bin/dto-sonar-entrypoint

ENV HOME=/home/sonar \
    PATH=/opt/sonar-scanner/bin:${PATH} \
    SONAR_USER_HOME=/tmp/sonar

USER 10001:10001
WORKDIR /workspace/project

ENTRYPOINT ["/usr/local/bin/dto-sonar-entrypoint"]
