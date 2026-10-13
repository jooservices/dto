#!/bin/sh
set -eu

if [ -z "${SONAR_TOKEN:-}" ]; then
    echo "SONAR_TOKEN is required from the local Sites environment or the GitHub Actions secret." >&2
    exit 1
fi

if [ "${SONAR_LOCAL:-false}" = true ]; then
    if [ "${SONAR_HOST_URL:-}" != "http://sonarqube:9000" ]; then
        echo "Local Sonar analysis must target the Shared Docker SonarQube service." >&2
        exit 1
    fi
elif [ "${GITHUB_ACTIONS:-false}" != true ] || [ "${SONAR_HOST_URL:-}" != "https://sonarcloud.io" ]; then
    echo "SonarCloud analysis is restricted to GitHub Actions." >&2
    exit 1
fi

if [ ! -s /workspace/project/coverage.xml ]; then
    echo "Sonar scan needs coverage.xml. Run make ci first." >&2
    exit 1
fi

exec sonar-scanner \
    -Dsonar.php.coverage.reportPaths=/workspace/project/coverage.xml \
    -Dsonar.working.directory=/tmp/sonar-work \
    -Dsonar.qualitygate.wait=true \
    -Dsonar.qualitygate.timeout=300 \
    "$@"
