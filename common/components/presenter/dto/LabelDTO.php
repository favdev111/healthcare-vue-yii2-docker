<?php

namespace common\components\presenter\dto;

use common\components\presenter\AbstractModel;

/**
 * Class LabelDTO
 * @package common\components\presenter\dto
 * @OA\Schema(
 *      type="object",
 *      required={"labelId", "name", "status", "color", "categoryId"}
 *     )
 */
class LabelDTO extends AbstractModel
{
    /**
     * @OA\Property(
     *          property="labelId",
     *          type="integer"
     *      ),
     * @var int
     */
    protected $labelId;
    /**
     * @OA\Property(
     *          property="name",
     *          type="string"
     *      ),
     * @var string
     */
    protected $name;

    /**
     * @OA\Property(
     *          property="color",
     *          type="string"
     *      ),
     * @var string
     */
    protected $color;

    /**
     * @OA\Property(
     *          property="categoryId",
     *          type="integer"
     *      ),
     * @var int
     */
    protected $categoryId;
    /**
     * @OA\Property(
     *          property="description",
     *          type="integer"
     *      ),
     * @var string|null
     */
    protected $description;
    /**
     * @OA\Property(
     *          property="itemId",
     *          type="integer"
     *      ),
     * @var int|null
     */
    protected $itemId;

    /**
     * @OA\Property(
     *          property="relatedId",
     *          type="integer"
     *      ),
     * @var int|null
     */
    protected $relatedId;
    /**
     * @var array
     */
    protected $skipNullFields = ['itemId','relatedId'];

    /**
     * LabelDTO constructor.
     * @param int $labelId
     * @param string $name
     * @param string $color
     * @param int $categoryId
     * @param string|null $description
     * @param int|null $itemId
     * @param int|null $relatedId
     */
    public function __construct(
        int $labelId,
        string $name,
        string $color,
        int $categoryId,
        ?string $description,
        ?int $itemId,
        ?int $relatedId
    ) {
        $this->labelId = $labelId;
        $this->name = $name;
        $this->categoryId = $categoryId;
        $this->color = $color;
        $this->itemId = $itemId;
        $this->relatedId = $relatedId;
        $this->description = $description;
    }
}
