<?php

namespace XinYin\UpgradeTool;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use XinYin\UpgradeTool\Helper\CommonHelper;

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
        $tags = collect(explode("\n", $output))
            ->filter()
            ->map(fn ($tag) => $this->formatTag($tag))
            ->sortByDesc('version')
            ->unique('commit');

        $message = "準備更新{$this->env_name}環境, 沒有對應標籤";
        if ($tags->isNotEmpty()) {
            $latest_version = $this->pluckLatestVersion($tags);
            $commits = $this->getCommitMessage($tags);

            $updated_commits = $commits
                            ->reject(fn (string $commit) => $this->isShouldFilteredCommit($commit))
                            ->map(fn (string $commit) => mb_substr(string: $commit, start: 8, encoding: 'utf8'))
                            ->join("\n");
    
            $message = "準備更新{$this->env_name}環境 \n版號: {$latest_version} \n更新內容：\n{$updated_commits} \n";
        }

        $message = str_replace('\n', PHP_EOL, $message);

        echo $message;

        shell_exec("echo '{$message}' | pbcopy");

        $this->sendWebHook($message);
    }

    /**
     * 獲得commit訊息
     *
     * @param Collection $tags
     *
     * @return Collection
     */
    protected function getCommitMessage(Collection $tags): Collection
    {
        $real_tags = $tags->take(2);
        $new_tag = $real_tags->first();
        $previous_tag = $real_tags->last();

        return collect(explode("\n", $this->getDiffCommit($new_tag['commit'], $previous_tag['commit'])));
    }

    /**
     * 獲得最新版本號
     *
     * @param Collection $tags
     *
     * @return string
     */
    protected function pluckLatestVersion(Collection $tags): string
    {
        return $tags->first()['version'];
    }

    /**
     * 是否為需要被篩選掉的commit
     *
     * @param string $commit
     *
     * @return bool
     */
    protected function isShouldFilteredCommit(string $commit): bool
    {
        return strpos($commit, 'Merge') || $commit === '';
    }

    /**
     * 格式化tag內的資料
     *
     * @param string $tag
     *
     * @return array
     */
    protected function formatTag(string $tag): array
    {
        $tag = str_replace('refs/tags/', '', $tag);

        $tag = substr($tag, 0, -32);

        [$version, $commit] = explode(' ', $tag);

        return [
            'version' => $version,
            'commit'  => $commit
        ];
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
     * @param string $version
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
     * 是否為重複的commit tag
     *
     * @param Collection $tags
     * @param string $commit
     *
     * @return bool
     */
    protected function isRepeatedCommit(Collection $tags, string $commit)
    {
        return $tags->filter(fn ($tag) => $tag['commit'] === $commit)->count() > 0;
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
