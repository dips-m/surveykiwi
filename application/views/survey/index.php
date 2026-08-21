<div class="sk-page-header">
    <div class="row">
        <div class="col-sm-12">
            <h1>Surveys</h1>
            <p class="text-muted">
                Manage your surveys and schedules.
            </p>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th width="60">#</th>
                    <th>Survey</th>
                    <th>Questions</th>
                    <th>Recurring</th>
                    <th>Participants</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th width="130">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($surveys)): ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            No surveys found.
                        </td>
                    </tr>

                <?php else: ?>
                    <?php foreach ($surveys as $index => $survey): ?>
                        <?php
                        $serial_number =
                            (($pagination['current_page'] - 1)
                            * $pagination['per_page'])
                            + $index
                            + 1;
                        ?>
                        <tr>
                            <td>
                                <?php echo $serial_number; ?>
                            </td>
                            <td>
                                <strong>
                                    <?php
                                    echo HTML::chars(
                                        $survey['title']
                                    );
                                    ?>
                                </strong>

                                <?php if ( ! empty($survey['description'])): ?>
                                <br>
                                    <small class="text-muted">
                                        <?php
                                        $words = explode(' ', $survey['description']);

                                        $truncated = count($words) > 5
                                            ? implode(' ', array_slice($words, 0, 5)) . '…'
                                            : $survey['description'];

                                        echo HTML::chars($truncated);
                                        ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                echo (int) $survey['questions'];
                                ?>
                            </td>
                            <td>
                                <?php if ( ! empty($survey['frequency'])): ?>
                                    <?php
                                    echo HTML::chars(
                                        ucfirst(
                                            $survey['frequency']
                                        )
                                    );
                                    ?>
                                <?php else: ?>
                                    <span class="text-muted">
                                        No
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                echo (int) $survey['participants'];
                                ?>
                            </td>
                            <td>
                                <?php
                                    $status = $survey['status'];

                                    $status_colors = array(
                                        'draft'     => '#999999',
                                        'published' => '#5cb85c',
                                        'closed'    => '#d9534f',
                                    );

                                    $dot_color = isset($status_colors[$status])
                                        ? $status_colors[$status]
                                        : '#999999';
                                ?>

                                <span
                                    style="display:inline-block; width:8px; height:8px; border-radius:50%; background-color:<?php echo $dot_color; ?>; margin-right:6px;"
                                ></span><?php echo HTML::chars(ucfirst($status)); ?>
                            </td>
                            <td>
                                <?php
                                if ( ! empty($survey['created_at']))
                                {
                                    echo HTML::chars(
                                        date(
                                            'M d, Y',
                                            strtotime(
                                                $survey['created_at']
                                            )
                                        )
                                    );
                                }
                                else
                                {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <a
                                    href="<?php
                                    echo URL::site(
                                        'survey/view/'
                                        . (int) $survey['id']
                                    );
                                    ?>"
                                    class="btn btn-xs btn-default"
                                    title="View Survey"
                                >
                                    <span
                                        class="glyphicon glyphicon-eye-open"
                                    ></span>
                                </a>
                                <a href="<?php
                                    echo URL::site(
                                        'survey/edit/'
                                        . (int) $survey['id']
                                    );
                                    ?>"
                                    class="btn btn-xs btn-default"
                                    title="Edit Survey"
                                >
                                    <span
                                        class="glyphicon glyphicon-pencil"
                                    ></span>
                                </a>

                                <!-- Public View -->
                                <a href="<?php echo 'survey/' . (int) $survey['id']; ?>"
                                    class="btn btn-xs btn-default"
                                    title="Public Survey Link"
                                    aria-label="Public Survey Link"
                                    target="_blank"
                                >
                                    <span class="glyphicon glyphicon-share"></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php
        $current_page = (int) $pagination['current_page'];
        $total_pages = (int) $pagination['total_pages'];

        $total = (int) $pagination['total'];
        $per_page = (int) $pagination['per_page'];

        if ($total > 0)
        {
            $start = (($current_page - 1) * $per_page) + 1;

            $end = min(
                $current_page * $per_page,
                $total
            );
        }
        else
        {
            $start = 0;
            $end = 0;
        }
        ?>

        <div class="sk-pagination">

            <p class="sk-pagination-info">
                Showing
                <?php echo $start; ?>
                -
                <?php echo $end; ?>
                of
                <?php echo $total; ?>
                surveys
            </p>

            <?php if ($total_pages > 1): ?>
                <ul class="pagination pagination-sm sk-pagination-links">
                    <?php if ($current_page > 1): ?>
                        <li>
                            <a
                                href="<?php
                                echo URL::site(
                                    'survey?page=' . ($current_page - 1)
                                );
                                ?>"
                                aria-label="Previous"
                            >
                                &laquo;
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li
                            class="<?php
                            echo ($i === $current_page)
                                ? 'active'
                                : '';
                            ?>"
                        >
                            <a
                                href="<?php
                                echo URL::site(
                                    'survey?page=' . $i
                                );
                                ?>"
                            >
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <li>
                            <a
                                href="<?php
                                echo URL::site(
                                    'survey?page=' . ($current_page + 1)
                                );
                                ?>"
                                aria-label="Next"
                            >
                                &raquo;
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>