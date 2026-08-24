import json
import math
import random
import sys
from collections import Counter, defaultdict
from typing import Any


def entropy(labels: list[str]) -> float:
    total = len(labels)

    if total == 0:
        return 0.0

    counts = Counter(labels)
    result = 0.0

    for count in counts.values():
        probability = count / total
        result -= probability * math.log2(probability)

    return result


def split_samples(
    samples: list[dict[str, Any]],
    ratio: float,
    classes: list[str],
    random_seed: int,
) -> tuple[list[dict[str, Any]], list[dict[str, Any]]]:
    """Membagi data secara stratified dengan total rasio sedekat mungkin 70:30.

    Setiap kelas tetap memiliki minimal satu data training dan satu data testing.
    Untuk dataset penelitian berukuran besar, jumlah total training akan tepat
    70% ketika pembulatan memungkinkan.
    """
    grouped: dict[str, list[dict[str, Any]]] = defaultdict(list)

    for sample in samples:
        label = sample.get("label")

        if label in classes:
            grouped[label].append(sample)

    generator = random.Random(random_seed)

    class_sizes: dict[str, int] = {}
    train_counts: dict[str, int] = {}
    fractional_parts: dict[str, float] = {}

    for class_name in classes:
        group = list(grouped.get(class_name, []))

        if len(group) < 2:
            raise ValueError(
                f"Kelas {class_name} minimal membutuhkan 2 data berlabel."
            )

        generator.shuffle(group)
        grouped[class_name] = group
        class_sizes[class_name] = len(group)

        desired = len(group) * ratio
        count = int(math.floor(desired))
        count = max(1, min(count, len(group) - 1))

        train_counts[class_name] = count
        fractional_parts[class_name] = desired - math.floor(desired)

    total_samples = sum(class_sizes.values())
    minimum_training = len(classes)
    maximum_training = total_samples - len(classes)
    target_training = int(round(total_samples * ratio))
    target_training = max(
        minimum_training,
        min(target_training, maximum_training),
    )

    current_training = sum(train_counts.values())

    if current_training < target_training:
        candidates = sorted(
            classes,
            key=lambda name: fractional_parts[name],
            reverse=True,
        )

        while current_training < target_training:
            changed = False

            for class_name in candidates:
                if train_counts[class_name] >= class_sizes[class_name] - 1:
                    continue

                train_counts[class_name] += 1
                current_training += 1
                changed = True

                if current_training >= target_training:
                    break

            if not changed:
                break

    elif current_training > target_training:
        candidates = sorted(
            classes,
            key=lambda name: fractional_parts[name],
        )

        while current_training > target_training:
            changed = False

            for class_name in candidates:
                if train_counts[class_name] <= 1:
                    continue

                train_counts[class_name] -= 1
                current_training -= 1
                changed = True

                if current_training <= target_training:
                    break

            if not changed:
                break

    training: list[dict[str, Any]] = []
    testing: list[dict[str, Any]] = []

    for class_name in classes:
        group = grouped[class_name]
        training_count = train_counts[class_name]

        training.extend(group[:training_count])
        testing.extend(group[training_count:])

    generator.shuffle(training)
    generator.shuffle(testing)

    if not training or not testing:
        raise ValueError(
            "Data training atau testing kosong."
        )

    return training, testing


def calculate_information_gain(
    samples: list[dict[str, Any]],
    features: list[str],
) -> list[dict[str, Any]]:
    base_entropy = entropy(
        [
            sample["label"]
            for sample in samples
        ]
    )

    results: list[dict[str, Any]] = []

    for feature in features:
        groups: dict[
            str,
            list[dict[str, Any]],
        ] = defaultdict(list)

        for sample in samples:
            value = str(
                sample
                .get("features", {})
                .get(feature, "Tidak Ada")
            )

            groups[value].append(sample)

        weighted_entropy = 0.0

        for group in groups.values():
            weighted_entropy += (
                len(group) /
                max(len(samples), 1)
            ) * entropy(
                [
                    sample["label"]
                    for sample in group
                ]
            )

        results.append(
            {
                "feature": feature,
                "gain": round(
                    base_entropy -
                    weighted_entropy,
                    10,
                ),
                "entropy_before": round(
                    base_entropy,
                    10,
                ),
                "entropy_after": round(
                    weighted_entropy,
                    10,
                ),
                "values": {
                    key: len(value)
                    for key, value in groups.items()
                },
            }
        )

    results.sort(
        key=lambda item: item["gain"],
        reverse=True,
    )

    for index, item in enumerate(
        results,
        start=1,
    ):
        item["ranking"] = index

    return results


def train_naive_bayes(
    samples: list[dict[str, Any]],
    features: list[str],
    classes: list[str],
) -> dict[str, Any]:
    class_counts = {
        class_name: 0
        for class_name in classes
    }

    feature_counts = {
        class_name: {
            feature: defaultdict(int)
            for feature in features
        }
        for class_name in classes
    }

    feature_values = {
        feature: set()
        for feature in features
    }

    for sample in samples:
        label = sample.get("label")

        if label not in classes:
            continue

        class_counts[label] += 1

        for feature in features:
            value = str(
                sample
                .get("features", {})
                .get(feature, "Tidak Ada")
            )

            feature_values[feature].add(value)

            feature_counts[
                label
            ][
                feature
            ][
                value
            ] += 1

    return {
        "total": len(samples),
        "classes": classes,
        "features": features,
        "class_counts": class_counts,
        "feature_counts": feature_counts,
        "feature_values": {
            feature: list(values)
            for feature, values
            in feature_values.items()
        },
    }



def serialize_model(model: dict[str, Any]) -> dict[str, Any]:
    """Mengubah struktur defaultdict menjadi JSON-serializable knowledge model."""
    return {
        "total": int(model.get("total", 0)),
        "classes": list(model.get("classes", [])),
        "features": list(model.get("features", [])),
        "class_counts": {
            class_name: int(count)
            for class_name, count in model.get("class_counts", {}).items()
        },
        "feature_counts": {
            class_name: {
                feature: {
                    str(value): int(count)
                    for value, count in counts.items()
                }
                for feature, counts in class_features.items()
            }
            for class_name, class_features in model.get("feature_counts", {}).items()
        },
        "feature_values": {
            feature: list(values)
            for feature, values in model.get("feature_values", {}).items()
        },
    }

def normalize_logs(
    logs: dict[str, float],
) -> dict[str, float]:
    maximum = max(
        logs.values()
    )

    exponentials: dict[
        str,
        float,
    ] = {}

    total = 0.0

    for class_name, value in logs.items():
        exponentials[class_name] = math.exp(
            value - maximum
        )

        total += exponentials[class_name]

    return {
        class_name: round(
            value /
            max(
                total,
                sys.float_info.min,
            ),
            6,
        )
        for class_name, value
        in exponentials.items()
    }


def predict(
    model: dict[str, Any],
    features: dict[str, Any],
) -> dict[str, Any]:
    logs: dict[str, float] = {}

    total_classes = len(
        model["classes"]
    )

    for class_name in model["classes"]:
        class_count = model[
            "class_counts"
        ].get(
            class_name,
            0,
        )

        log_probability = math.log(
            (class_count + 1) /
            (
                model["total"] +
                total_classes
            )
        )

        for feature in model["features"]:
            value = str(
                features.get(
                    feature,
                    "Tidak Ada",
                )
            )

            known_values = model[
                "feature_values"
            ].get(
                feature,
                [],
            )

            value_count = model[
                "feature_counts"
            ][
                class_name
            ][
                feature
            ].get(
                value,
                0,
            )

            value_cardinality = len(
                known_values
            )

            if value not in known_values:
                value_cardinality += 1

            value_cardinality = max(
                value_cardinality,
                1,
            )

            log_probability += math.log(
                (value_count + 1) /
                (
                    class_count +
                    value_cardinality
                )
            )

        logs[class_name] = log_probability

    probabilities = normalize_logs(
        logs
    )

    predicted_class = max(
        probabilities,
        key=probabilities.get,
    )

    return {
        "class": predicted_class,
        "probability":
            probabilities[predicted_class],
        "probabilities":
            probabilities,
    }


def evaluate(
    samples: list[dict[str, Any]],
    model: dict[str, Any],
    classes: list[str],
) -> dict[str, Any]:
    matrix = {
        actual: {
            predicted: 0
            for predicted in classes
        }
        for actual in classes
    }

    for sample in samples:
        actual = sample.get("label")

        if actual not in classes:
            continue

        predicted = predict(
            model,
            sample.get("features", {}),
        )["class"]

        matrix[
            actual
        ][
            predicted
        ] += 1

    valid_total = sum(
        sum(row.values())
        for row in matrix.values()
    )

    if valid_total == 0:
        raise ValueError(
            "Data testing valid tidak tersedia."
        )

    correct = 0
    precision_total = 0.0
    recall_total = 0.0
    f1_total = 0.0

    per_class: dict[
        str,
        dict[str, float | int],
    ] = {}

    for class_name in classes:
        tp = matrix[
            class_name
        ][
            class_name
        ]

        fp = sum(
            matrix[
                other
            ][
                class_name
            ]
            for other in classes
            if other != class_name
        )

        fn = sum(
            matrix[
                class_name
            ][
                other
            ]
            for other in classes
            if other != class_name
        )

        support = sum(
            matrix[class_name].values()
        )

        precision = (
            tp / (tp + fp)
            if (tp + fp) > 0
            else 0.0
        )

        recall = (
            tp / (tp + fn)
            if (tp + fn) > 0
            else 0.0
        )

        f1 = (
            2 * precision * recall /
            (precision + recall)
            if (precision + recall) > 0
            else 0.0
        )

        correct += tp
        precision_total += precision
        recall_total += recall
        f1_total += f1

        per_class[class_name] = {
            "precision": round(
                precision * 100,
                2,
            ),
            "recall": round(
                recall * 100,
                2,
            ),
            "f1_score": round(
                f1 * 100,
                2,
            ),
            "support": support,
        }

    return {
        "akurasi": round(
            (correct / valid_total) * 100,
            2,
        ),
        "precision": round(
            (
                precision_total /
                len(classes)
            ) * 100,
            2,
        ),
        "recall": round(
            (
                recall_total /
                len(classes)
            ) * 100,
            2,
        ),
        "f1_score": round(
            (
                f1_total /
                len(classes)
            ) * 100,
            2,
        ),
        "confusion_matrix": matrix,
        "per_class": per_class,
        "features": model["features"],
    }



def build_predictions(
    prediction_samples: list[dict[str, Any]],
    baseline_model: dict[str, Any],
    optimized_model: dict[str, Any],
) -> list[dict[str, Any]]:
    predictions: list[dict[str, Any]] = []

    for sample in prediction_samples:
        sample_features = sample.get(
            "features",
            {},
        )

        baseline = predict(
            baseline_model,
            sample_features,
        )

        optimized = predict(
            optimized_model,
            sample_features,
        )

        predictions.append(
            {
                "siswa_id": sample["siswa_id"],
                "jumlah_pelanggaran": sample["jumlah_pelanggaran"],
                "total_poin": sample["total_poin"],
                "label": sample.get("label"),
                "features": sample_features,
                "baseline": baseline,
                "optimized": optimized,
            }
        )

    return predictions

def main() -> None:
    try:
        raw_payload = sys.stdin.read().strip()

        if not raw_payload:
            raise ValueError(
                "Payload dari Laravel kosong."
            )

        payload = json.loads(
            raw_payload
        )

        mode = str(payload.get("mode", "train")).lower()

        labeled_samples = payload.get(
            "labeled_samples",
            [],
        )

        prediction_samples = payload.get(
            "prediction_samples",
            [],
        )

        features = payload.get(
            "features",
            [],
        )

        classes = payload.get(
            "classes",
            [
                "Baik",
                "Butuh Perhatian",
                "Bermasalah",
            ],
        )

        training_ratio = float(
            payload.get(
                "training_ratio",
                0.7,
            )
        )

        random_seed = int(
            payload.get(
                "random_seed",
                42,
            )
        )

        if abs(training_ratio - 0.7) > 1e-9:
            raise ValueError(
                "Rasio training/testing wajib 70:30 (training_ratio = 0.70)."
            )

        training_ratio = 0.7

        if not prediction_samples:
            raise ValueError(
                "Data siswa yang akan diprediksi kosong."
            )

        if mode == "predict":
            baseline_model = payload.get("baseline_model")
            optimized_model = payload.get("optimized_model")

            if not isinstance(baseline_model, dict) or not baseline_model:
                raise ValueError("Knowledge model Naive Bayes belum tersedia.")

            if not isinstance(optimized_model, dict) or not optimized_model:
                raise ValueError("Knowledge model Naive Bayes + Information Gain belum tersedia.")

            predictions = build_predictions(
                prediction_samples,
                baseline_model,
                optimized_model,
            )

            print(
                json.dumps(
                    {
                        "success": True,
                        "message": "Klasifikasi real-time berhasil menggunakan knowledge model tersimpan.",
                        "mode": "predict",
                        "total_samples": len(prediction_samples),
                        "predictions": predictions,
                    },
                    ensure_ascii=False,
                )
            )

            return

        if mode != "train":
            raise ValueError("Mode Python tidak valid. Gunakan train atau predict.")

        if not features:
            raise ValueError(
                "Daftar fitur kosong."
            )

        training, testing = split_samples(
            labeled_samples,
            training_ratio,
            classes,
            random_seed,
        )

        gain_results = calculate_information_gain(
            training,
            features,
        )

        selected_features = [
            item["feature"]
            for item in gain_results
            if item["gain"] > 0
        ][:3]

        if not selected_features:
            selected_features = features

        baseline_model = train_naive_bayes(
            training,
            features,
            classes,
        )

        optimized_model = train_naive_bayes(
            training,
            selected_features,
            classes,
        )

        predictions = build_predictions(
            prediction_samples,
            baseline_model,
            optimized_model,
        )

        output = {
            "success": True,

            "message":
                "Klasifikasi berhasil diproses menggunakan Python.",

            "total_samples":
                len(prediction_samples),

            "total_labeled_samples":
                len(labeled_samples),

            "training_count":
                len(training),

            "testing_count":
                len(testing),

            "training_ratio":
                training_ratio,

            "random_seed":
                random_seed,

            "selected_features":
                selected_features,

            "baseline_model":
                serialize_model(baseline_model),

            "optimized_model":
                serialize_model(optimized_model),

            "gain_results":
                gain_results,

            "predictions":
                predictions,

            "baseline_evaluation":
                evaluate(
                    testing,
                    baseline_model,
                    classes,
                ),

            "optimized_evaluation":
                evaluate(
                    testing,
                    optimized_model,
                    classes,
                ),
        }

        print(
            json.dumps(
                output,
                ensure_ascii=False,
            )
        )

    except Exception as exception:
        print(
            json.dumps(
                {
                    "success": False,
                    "message": str(exception),
                },
                ensure_ascii=False,
            )
        )


if __name__ == "__main__":
    main()