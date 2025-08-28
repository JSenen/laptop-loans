<?php
namespace App\Services;

class AiService {
  private static function post(string $url, array $payload): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_POST => 1,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_TIMEOUT => 60,
    ]);
    $out = curl_exec($ch);
    if ($out === false) throw new \RuntimeException('Ollama error: '.curl_error($ch));
    curl_close($ch);
    return json_decode($out, true) ?: [];
  }

  // Chat con historial
  // app/Services/AiService.php
public static function chat(array $messages, string $model='llama3:8b', string $system=null): string {
  $system = $system ?? 'Responde SIEMPRE en ESPAÑOL. '
    .'Eres un asistente para un gestor de préstamos de portátiles. Sé conciso.';
  $payload = [
    'model' => $model,
    'messages' => array_merge([['role'=>'system','content'=>$system]], $messages),
    'stream' => false,
    'options' => [
      'temperature' => 0.1,   // más determinista
      'num_ctx'     => 4096
    ],
  ];
  return self::post('http://127.0.0.1:11434/api/chat', $payload)['message']['content'] ?? '';
}


  // Embeddings para búsqueda semántica
  public static function embed(string $text, string $model='nomic-embed-text'): array {
    $resp = self::post('http://127.0.0.1:11434/api/embeddings', ['model'=>$model,'prompt'=>$text]);
    return $resp['embedding'] ?? [];
  }
}
