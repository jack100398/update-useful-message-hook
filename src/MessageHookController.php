<?php

namespace UpdateUseful\MessageHook;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;

class MessageHookController extends Controller
{
    /** @var string 環境名稱 */
    private $env_name;

    /** @var string 可辨識符號 */
    private $symbol;

    protected array $settings = [];

    protected $symbols = [];

    public function __construct(string $env)
    {
        $this->settings = CommonHelper::getEnvSettings();
        $this->symbols = collect($this->settings)
            ->reject(fn (array $setting) => is_null($setting['symbol']) || $setting['symbol'] === '')
            ->pluck('symbol');

        $setting = Arr::get($this->settings, $env);

        $this->symbol = $setting['symbol'];
        $this->env_name = $setting['name'];
    }

    /**
     * 更新環境
     *
     * @return void
     */
    public function updateEnv()
    {
        $path = base_path();
        // Change to base directory
        chdir($path);

        // 更新tag
        shell_exec('git fetch -t -f');

        $output = shell_exec('git for-each-ref --sort=creatordate --format="%(refname) %(objectname)" refs/tags');

        // 取得最新的tag
        $tags = explode("\n", $output);

        $real_tags = collect();
        foreach ($tags as $tag) {
            if ($tag === '') continue;

            $tag = str_replace('refs/tags/', '', $tag);

            $tag = substr($tag, 0, -32);

            [$version, $commit] = explode(' ', $tag);

            if ($this->isNeedsTag($version)) {
                $real_tags->prepend([
                    'version' => $version,
                    'commit' => $commit
                ]);
            }
        }

        $real_tags = $real_tags->take(2);
        $new_tag = $real_tags->first();
        $previous_tag = $real_tags->last();

        $commits = explode("\n", $this->getDiffCommit($new_tag['commit'], $previous_tag['commit']));

        $real_commits = [];

        foreach ($commits as $commit) {
            if (strpos($commit, 'Merge') || $commit === '') {
                continue;
            }

            $real_commits[] = mb_substr(string: $commit, start: 8, encoding: 'utf8');
        }
        //
        $updated_commits = implode("\n", $real_commits);

        $message = "準備更新{$this->env_name}環境 \n版號: {$new_tag['version']} \n更新內容：\n{$updated_commits}";

        $this->sendWebHook($message);
    }

    /**
     * 獲得最新版本與上一版本之間的差異
     *
     * @param string $last_commit
     * @param string $previous_commit
     *
     * @return string
     */
    protected function getDiffCommit(string $last_commit, string $previous_commit): string
    {
        if ($last_commit === $previous_commit) {
            return shell_exec("git log {$last_commit} --oneline");
        }
        return shell_exec("git log {$last_commit}...{$previous_commit} --oneline");
    }

    /**
     * 篩選版本是否為需要部署的環境的tag
     *
     * @param string
     *
     * @return bool
     */
    protected function isNeedsTag(string $version): bool
    {
        if (is_null($this->symbol) || $this->symbol === '') {
            return $this->symbols->filter(fn (string $symbol) => strpos($version, $symbol))->isEmpty();
        } else {
            return ! (strpos($version, $this->symbol) === false);
        }
    }

    /**
     * 發送webhook
     *
     * @param string $text
     * @return void
     */
    public function sendWebHook(string $text)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => CommonHelper::getUrlSettings(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{"text": "' . $text . '"}',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
}
